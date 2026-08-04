<?php
// Save as: app/Http/Controllers/MonthlyFeeGeneratorController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\PaClass;
use App\Models\PaSession;
use App\Models\PaEnrollment;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherItem;
use App\Models\ClassFeeStructure;
use App\Models\FeeType;

class MonthlyFeeGeneratorController extends Controller
{
    public function create()
    {
        $classes  = PaClass::where('is_active', 1)->orderBy('class_order')->get();
        $sessions = PaSession::where('is_active', 1)->get();

        return view('monthly_fee_generator.create', compact('classes', 'sessions'));
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW — build the exact list of vouchers that WOULD be generated,
    | without writing anything to the database. Lets Danish see, per
    | student, the fee amount, whether a voucher already exists for this
    | month (will be skipped), and — if requested — the previous balance
    | that will be added on top, including exactly which old voucher(s)
    | it's coming from. Confirms on the next screen via store().
    |--------------------------------------------------------------------------
    */

    public function preview(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:pa_classes,id',
            'session_id' => 'required|exists:pa_sessions,id',
            'fee_month'  => 'required',
            'due_date'   => 'required|date',
        ]);

        $rows = $this->buildRows($request);

        $class   = PaClass::find($request->class_id);
        $session = PaSession::find($request->session_id);

        // Same "monthly category only" filter as buildRows(), shown here so
        // it's obvious on screen exactly which fee-type lines make up the
        // per-student amount — catches mis-tagged fee types at a glance.
        $feeStructure = ClassFeeStructure::with('feeType')
            ->where('class_id', $request->class_id)
            ->where('is_active', 1)
            ->whereHas('feeType', function ($q) {
                $q->where('category', 'monthly');
            })
            ->get();

        return view('monthly_fee_generator.preview', [
            'rows'                     => $rows,
            'class'                    => $class,
            'session'                  => $session,
            'fee_month'                => $request->fee_month,
            'due_date'                 => $request->due_date,
            'include_previous_balance' => (bool) $request->include_previous_balance,
            'feeStructure'             => $feeStructure,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:pa_classes,id',
            'session_id' => 'required|exists:pa_sessions,id',
            'fee_month'  => 'required',          // matches <input name="fee_month">
            'due_date'   => 'required|date',
        ]);

        $month = $request->fee_month;            // e.g. "2026-05"

        $monthlyFeeType = FeeType::where('name', 'Monthly Fee')->first();

        if (!$monthlyFeeType) {
            return back()->with(
                'error',
                'Fee type "Monthly Fee" not found. Please set up fee types first.'
            );
        }

        $rows = $this->buildRows($request);

        if ($rows->isEmpty()) {
            return back()->with(
                'error',
                'No active students found for the selected class and session.'
            );
        }

        $generated               = 0;
        $skipped                 = 0;
        $studentsWithPrevBalance = 0;
        $totalPrevBalanceAdded   = 0;

        /*
        |----------------------------------------------------------------------
        | Wrap the ENTIRE generation in a single transaction.
        | If any voucher fails, the whole batch rolls back — no orphans.
        |----------------------------------------------------------------------
        */
        DB::transaction(function () use (
            $rows, $monthlyFeeType, $month, $request,
            &$generated, &$skipped, &$studentsWithPrevBalance, &$totalPrevBalanceAdded
        ) {
            foreach ($rows as $row) {

                if ($row['already_exists']) {
                    $skipped++;
                    continue;
                }

                if ($row['fee_amount'] <= 0) {
                    // Nothing configured for this student/class — skip rather
                    // than generate a Rs. 0 voucher.
                    $skipped++;
                    continue;
                }

                $student       = $row['enrollment']->student;
                $discount      = $row['discount'];
                $totalAmount   = $row['fee_amount'];
                $payableAmount = max(0, $totalAmount - $discount);

                $periodFrom = $month . '-01';
                $periodTo   = date('Y-m-t', strtotime($periodFrom));

                $voucher = FeeVoucher::create([
                    'voucher_no'     => 'MF-' . date('YmdHis') . '-' . $student->id,
                    'student_id'     => $student->id,
                    'voucher_type'   => 'monthly',
                    'period_from'    => $request->period_from ?? $periodFrom,
                    'period_to'      => $request->period_to   ?? $periodTo,
                    'total_amount'   => $totalAmount,
                    'discount'       => $discount,
                    'payable_amount' => $payableAmount,
                    'paid_amount'    => 0,
                    'balance_amount' => $payableAmount,
                    'due_date'       => $request->due_date,
                    'status'         => 'unpaid',
                    'notes'          => 'Auto-generated for ' . $month,
                ]);

                // Create voucher items from fee structure
                if ($row['fee_structure']->isNotEmpty()) {

                    foreach ($row['fee_structure'] as $fee) {
                        FeeVoucherItem::create([
                            'voucher_id'   => $voucher->id,
                            'fee_type_id'  => $fee->fee_type_id,
                            'description'  => $fee->feeType->name ?? null,
                            'month'        => $month . '-01',
                            'months_count' => 1,
                            'amount'       => $fee->amount,
                        ]);
                    }

                } else {

                    // Fallback: single monthly fee line
                    FeeVoucherItem::create([
                        'voucher_id'   => $voucher->id,
                        'fee_type_id'  => $monthlyFeeType->id,
                        'description'  => 'Monthly Fee',
                        'month'        => $month . '-01',
                        'months_count' => 1,
                        'amount'       => $totalAmount,
                    ]);
                }

                /*
                |------------------------------------------------------------
                | Previous Balance — added as an EXTRA line item on the same
                | voucher (mirrors the manual "Create Voucher" screen), then
                | rolled into the voucher's total/payable/balance amounts.
                |
                | Every OLD voucher that contributed to this sum is then
                | closed out via markAsCarriedForwardTo():
                |   - status -> 'carried_forward' (shows as C.F in the UI)
                |   - balance_amount -> 0 (so it can never be double-counted
                |     in the ledger, dashboard, or defaulter reports again)
                |   - notes gets the new voucher number appended
                |   - carried_forward_to_voucher_id links to the new voucher
                |
                | The NEW voucher's previous_balance_voucher_id references
                | the single LATEST source voucher (by due_date) for display,
                | even when several old vouchers were rolled in together.
                |------------------------------------------------------------
                */
                if ($request->include_previous_balance && $row['previous_balance'] > 0) {

                    $prevBalanceFeeType = FeeType::firstOrCreate(
                        ['name' => 'Previous Balance'],
                        ['category' => 'other', 'is_active' => 1, 'description' => 'Previous outstanding balance']
                    );

                    /** @var \Illuminate\Support\Collection $sourceVouchers */
                    $sourceVouchers = $row['previous_balance_vouchers'];
                    $latestSource   = $sourceVouchers->first(); // ordered latest due_date first, see buildRows()

                    $refLabel = $latestSource
                        ? ' (Ref: ' . $latestSource->voucher_no
                            . ($sourceVouchers->count() > 1 ? ' +' . ($sourceVouchers->count() - 1) . ' more' : '')
                            . ')'
                        : '';

                    FeeVoucherItem::create([
                        'voucher_id'   => $voucher->id,
                        'fee_type_id'  => $prevBalanceFeeType->id,
                        'description'  => 'Previous outstanding balance (b/f)' . $refLabel,
                        'month'        => $periodFrom,
                        'months_count' => 1,
                        'amount'       => $row['previous_balance'],
                    ]);

                    $newTotal = $voucher->payable_amount + $row['previous_balance'];

                    $voucher->update([
                        'total_amount'                => $voucher->total_amount + $row['previous_balance'],
                        'payable_amount'               => $newTotal,
                        'balance_amount'               => $newTotal,
                        'previous_balance_voucher_id'  => $latestSource?->id,
                    ]);

                    // Close out every contributing voucher so its balance
                    // stops showing as separately outstanding.
                    foreach ($sourceVouchers as $sourceVoucher) {
                        $sourceVoucher->markAsCarriedForwardTo($voucher);
                    }

                    $studentsWithPrevBalance++;
                    $totalPrevBalanceAdded += $row['previous_balance'];
                }

                $generated++;
            }
        });

        $message = "{$generated} voucher(s) generated successfully.";

        if ($skipped > 0) {
            $message .= " {$skipped} student(s) skipped (already generated or no fee configured).";
        }

        if ($studentsWithPrevBalance > 0) {
            $message .= " Previous balance included for {$studentsWithPrevBalance} student(s), totalling Rs. "
                . number_format($totalPrevBalanceAdded, 0) . ". Their old voucher(s) were marked Carried Forward.";
        }

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | Shared row-builder used by BOTH preview() and store() so the numbers
    | shown on the preview screen are guaranteed to match what gets created.
    |--------------------------------------------------------------------------
    */

    private function buildRows(Request $request)
    {
        $month      = $request->fee_month;
        $periodFrom = $month . '-01';

        $enrollments = PaEnrollment::with('student')
            ->where('class_id', $request->class_id)
            ->where('session_id', $request->session_id)
            ->where('is_active', 1)
            ->where('status', 'active')
            // The enrollment being active doesn't guarantee the student is —
            // a student can be deactivated (e.g. left/withdrawn) without
            // their old enrollment record being flipped too. Filter on both,
            // otherwise inactive students still get picked up and billed.
            ->whereHas('student', function ($q) {
                $q->where('is_active', 1);
            })
            ->get()
            ->filter(fn ($e) => $e->student !== null && $e->student->is_active == 1)
            ->sortBy(fn ($e) => $e->student->student_name);

        // Only recurring monthly fee-type rows belong in a monthly voucher.
        // Without this filter, one-time fee types on the same class's
        // structure (e.g. an "Admission Fee" tagged category = 'admission')
        // were being summed in too, inflating the monthly amount.
        $feeStructure = ClassFeeStructure::with('feeType')
            ->where('class_id', $request->class_id)
            ->where('is_active', 1)
            ->whereHas('feeType', function ($q) {
                $q->where('category', 'monthly');
            })
            ->get();

        $structureTotal = $feeStructure->sum('amount');

        return $enrollments->map(function ($enrollment) use ($month, $periodFrom, $feeStructure, $structureTotal, $request) {

            $student = $enrollment->student;

            $alreadyExists = FeeVoucher::where('student_id', $student->id)
                ->where('voucher_type', 'monthly')
                ->whereHas('items', function ($q) use ($month) {
                    $q->where('month', $month . '-01');
                })
                ->exists();

            $feeAmount = $structureTotal > 0
                ? $structureTotal
                : ($enrollment->monthly_fee ?? 0);

            /*
            |------------------------------------------------------------------
            | Previous balance — fetches the ACTUAL source voucher records
            | (not just a sum), ordered latest-due-date-first. This mirrors
            | the "Previous Balances" report/screen so the two stay in
            | agreement, and lets store() reference + close out the exact
            | vouchers the balance came from.
            |------------------------------------------------------------------
            */
            $previousBalanceVouchers = $request->include_previous_balance
                ? FeeVoucher::where('student_id', $student->id)
                    ->outstanding()
                    ->where('due_date', '<', $periodFrom)
                    ->orderByDesc('due_date')
                    ->orderByDesc('id')
                    ->get()
                : collect();

            $previousBalance = (float) $previousBalanceVouchers->sum('balance_amount');

            return [
                'enrollment'                => $enrollment,
                'already_exists'            => $alreadyExists,
                'fee_structure'             => $feeStructure,
                'fee_amount'                => $feeAmount,
                'discount'                  => $enrollment->discount_amount ?? 0,
                'previous_balance'          => $previousBalance,
                'previous_balance_vouchers' => $previousBalanceVouchers,
                'payable'                   => max(0, $feeAmount - ($enrollment->discount_amount ?? 0)) + $previousBalance,
            ];
        })->values();
    }
}
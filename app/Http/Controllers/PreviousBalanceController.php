<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherItem;
use App\Models\FeeType;
use App\Models\PaEnrollment;
use Carbon\Carbon;

class PreviousBalanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List all students with outstanding previous balance
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $cutoffDate  = Carbon::now()->startOfMonth()->toDateString();
        $classFilter = $request->class_id;

        /*
        |----------------------------------------------------------------------
        | Find all vouchers that are unpaid/partial and overdue
        |----------------------------------------------------------------------
        */

        $query = FeeVoucher::with(['student.activeEnrollment.class', 'student.activeEnrollment.session'])
            ->outstanding()
            ->where('due_date', '<', $cutoffDate)
            ->orderBy('student_id')
            ->orderBy('due_date');

        $overdueVouchers = $query->get();

        /*
        |----------------------------------------------------------------------
        | Group by student and sum up balance
        |----------------------------------------------------------------------
        */

        $studentBalances = $overdueVouchers
            ->groupBy('student_id')
            ->map(function ($vouchers) {
                $student         = $vouchers->first()->student;
                $enrollment      = $student?->activeEnrollment;
                $totalBalance    = $vouchers->sum('balance_amount');
                $oldestVoucher   = $vouchers->sortBy('due_date')->first();
                $voucherCount    = $vouchers->count();

                return [
                    'student'         => $student,
                    'enrollment'      => $enrollment,
                    'vouchers'        => $vouchers,
                    'total_balance'   => $totalBalance,
                    'oldest_due_date' => $oldestVoucher?->due_date,
                    'voucher_count'   => $voucherCount,
                    'months_overdue'  => $oldestVoucher
                        ? Carbon::parse($oldestVoucher->due_date)->diffInMonths(now())
                        : 0,
                ];
            })
            ->sortByDesc('total_balance');

        // Class filter (post-collection since it's nested)
        if ($classFilter) {
            $studentBalances = $studentBalances->filter(function ($row) use ($classFilter) {
                return $row['enrollment']?->class_id == $classFilter;
            });
        }

        $grandTotal = $studentBalances->sum('total_balance');

        // Classes for filter dropdown
        $classes = \App\Models\PaClass::orderBy('class_order')->get();

        return view('previous_balance.index', compact(
            'studentBalances',
            'grandTotal',
            'classes',
            'classFilter',
            'cutoffDate'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Carry Forward — add previous balance as a new voucher line
    | This creates a "Previous Balance" voucher for the current month
    |--------------------------------------------------------------------------
    */

    public function carryForward(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $studentId  = $request->student_id;
        $cutoffDate = Carbon::now()->startOfMonth()->toDateString();

        /*
        |----------------------------------------------------------------------
        | Pre-checks BEFORE the transaction (so we can return clean errors)
        |----------------------------------------------------------------------
        */

        $overdueVouchers = FeeVoucher::where('student_id', $studentId)
            ->outstanding()
            ->where('due_date', '<', $cutoffDate)
            ->get();

        if ($overdueVouchers->isEmpty()) {
            return back()->with('error', 'No outstanding balance found for this student.');
        }

        $alreadyExists = FeeVoucher::where('student_id', $studentId)
            ->where('voucher_type', 'manual')
            ->where('notes', 'like', '%Previous Balance%')
            ->where('period_from', $cutoffDate)
            ->exists();

        if ($alreadyExists) {
            return back()->with('error', 'Previous balance already carried forward for this month.');
        }

        $totalBalance = $overdueVouchers->sum('balance_amount');

        /*
        |----------------------------------------------------------------------
        | Now execute the actual DB writes inside a transaction.
        | The pre-checks ensure this should succeed cleanly.
        |----------------------------------------------------------------------
        */

        DB::transaction(function () use ($studentId, $cutoffDate, $totalBalance, $overdueVouchers) {
            $feeType = FeeType::firstOrCreate(
                ['name' => 'Previous Balance'],
                ['category' => 'other', 'is_active' => 1, 'description' => 'Auto carry-forward of previous balance']
            );

            $voucher = FeeVoucher::create([
                'voucher_no'     => 'CF-' . date('YmdHis') . '-' . $studentId,
                'student_id'     => $studentId,
                'voucher_type'   => 'manual',
                'period_from'    => $cutoffDate,
                'period_to'      => Carbon::now()->endOfMonth()->toDateString(),
                'total_amount'   => $totalBalance,
                'discount'       => 0,
                'payable_amount' => $totalBalance,
                'paid_amount'    => 0,
                'balance_amount' => $totalBalance,
                'due_date'       => Carbon::now()->endOfMonth()->toDateString(),
                'status'         => 'unpaid',
                'notes'          => 'Previous Balance carry-forward (' . $overdueVouchers->count() . ' voucher(s))',
            ]);

            FeeVoucherItem::create([
                'voucher_id'   => $voucher->id,
                'fee_type_id'  => $feeType->id,
                'description'  => 'Previous Balance carry-forward',
                'month'        => $cutoffDate,
                'months_count' => 1,
                'amount'       => $totalBalance,
            ]);
        });

        return back()->with('success', "Previous balance of Rs. " . number_format($totalBalance, 0) . " carried forward successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk carry-forward (all students with outstanding balances)
    |--------------------------------------------------------------------------
    */

    public function bulkCarryForward(Request $request)
    {
        $cutoffDate = Carbon::now()->startOfMonth()->toDateString();

        $studentIds = FeeVoucher::outstanding()
            ->where('due_date', '<', $cutoffDate)
            ->distinct()
            ->pluck('student_id');

        $processed = 0;
        $skipped   = 0;

        /*
        |----------------------------------------------------------------------
        | Wrap the ENTIRE bulk operation in a single transaction.
        |----------------------------------------------------------------------
        */
        DB::transaction(function () use ($studentIds, $cutoffDate, &$processed, &$skipped) {

            foreach ($studentIds as $studentId) {

                // Skip if already carried forward this month
                $alreadyExists = FeeVoucher::where('student_id', $studentId)
                    ->where('voucher_type', 'manual')
                    ->where('notes', 'like', '%Previous Balance%')
                    ->where('period_from', $cutoffDate)
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                $totalBalance = FeeVoucher::where('student_id', $studentId)
                    ->outstanding()
                    ->where('due_date', '<', $cutoffDate)
                    ->sum('balance_amount');

                if ($totalBalance <= 0) {
                    continue;
                }

                $feeType = FeeType::firstOrCreate(
                    ['name' => 'Previous Balance'],
                    ['category' => 'other', 'is_active' => 1]
                );

                $voucher = FeeVoucher::create([
                    'voucher_no'     => 'CF-' . date('Ymd') . '-' . $studentId,
                    'student_id'     => $studentId,
                    'voucher_type'   => 'manual',
                    'period_from'    => $cutoffDate,
                    'period_to'      => Carbon::now()->endOfMonth()->toDateString(),
                    'total_amount'   => $totalBalance,
                    'discount'       => 0,
                    'payable_amount' => $totalBalance,
                    'paid_amount'    => 0,
                    'balance_amount' => $totalBalance,
                    'due_date'       => Carbon::now()->endOfMonth()->toDateString(),
                    'status'         => 'unpaid',
                    'notes'          => 'Previous Balance carry-forward (bulk)',
                ]);

                FeeVoucherItem::create([
                    'voucher_id'   => $voucher->id,
                    'fee_type_id'  => $feeType->id,
                    'description'  => 'Previous Balance carry-forward',
                    'month'        => $cutoffDate,
                    'months_count' => 1,
                    'amount'       => $totalBalance,
                ]);

                $processed++;
            }
        });

        return back()->with('success', "{$processed} student(s) carry-forwarded. {$skipped} already done.");
    }
}
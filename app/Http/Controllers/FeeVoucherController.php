<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\PaClass;
use App\Models\FeeType;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherItem;
use Carbon\Carbon;

class FeeVoucherController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
public function index(Request $request)
    
{
    $search        = $request->search;
    $query         = $search;
    $statusFilter  = $request->status;
    $studentFilter = $request->student_id;
    $classFilter   = $request->class_id;
    $monthFilter   = $request->month;
    $sortFilter    = $request->sort ?? 'latest';

    $vouchers = FeeVoucher::with([
        'student',
        'student.enrollments.class',
        'payments'          // needed for "Fix Method" button on paid vouchers
    ])

    ->when($search, function ($q) use ($search) {

        $q->where(function ($sub) use ($search) {

            $sub->where('voucher_no', 'like', "%{$search}%")

                ->orWhereHas('student', function ($sq) use ($search) {

                    $sq->where('student_name', 'like', "%{$search}%")
                       ->orWhere('admission_no', 'like', "%{$search}%");

                });

        });

    })

    ->when($statusFilter, function ($q) use ($statusFilter) {

        $q->where('status', strtolower($statusFilter));

    })

    ->when($studentFilter, function ($q) use ($studentFilter) {

        $q->where('student_id', $studentFilter);

    })

    ->when($classFilter, function ($q) use ($classFilter) {

        $q->whereHas('student.enrollments', function ($enrollment) use ($classFilter) {

            $enrollment->where('class_id', $classFilter);

        });

    })

    ->when($monthFilter, function ($q) use ($monthFilter) {

        $q->whereMonth('period_from', $monthFilter);

    });

    // Sorting

    if ($sortFilter == 'oldest') {

        $vouchers->orderBy('created_at', 'asc');

    } else {

        $vouchers->orderBy('created_at', 'desc');

    }

    $vouchers = $vouchers->paginate(25)->withQueryString();

    $students = Student::where('is_active', 1)
        ->orderBy('student_name')
        ->get();

    $classes = PaClass::orderBy('class_order')->get();

    return view('fee_vouchers.index', compact(
        'vouchers',
        'query',
        'search',
        'statusFilter',
        'studentFilter',
        'classFilter',
        'monthFilter',
        'sortFilter',
        'students',
        'classes'
    ));
}

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $classes  = PaClass::orderBy('class_order')->get();
        $students = Student::with('enrollments.class')
            ->where('is_active', 1)
            ->orderBy('student_name')
            ->get();
        $feeTypes = FeeType::where('is_active', 1)->get();

        $preselectedStudentId   = $request->student_id;
        $preselectedPrevBalance = 0;
        $preselectedOverdue     = [];   // plain array — safe to json_encode in Blade

        if ($preselectedStudentId) {

            $cutoff = Carbon::now()->startOfMonth()->toDateString();

            $preselectedPrevBalance = FeeVoucher::where('student_id', $preselectedStudentId)
                ->outstanding()
                ->where('due_date', '<', $cutoff)
                ->sum('balance_amount');

            // Map to plain array HERE in the controller — avoids Blade/PHP arrow-function parse conflict
            $preselectedOverdue = FeeVoucher::where('student_id', $preselectedStudentId)
                ->outstanding()
                ->where('due_date', '<', $cutoff)
                ->orderBy('due_date')
                ->get()
                ->map(function ($v) {
                    return [
                        'voucher_no'     => $v->voucher_no,
                        'due_date'       => Carbon::parse($v->due_date)->format('M Y'),
                        'payable_amount' => (float) $v->payable_amount,
                        'paid_amount'    => (float) $v->paid_amount,
                        'balance_amount' => (float) $v->balance_amount,
                        'status'         => $v->status,
                    ];
                })
                ->values()
                ->toArray();
        }

        // Build a class→feeType→unitRate lookup for JS auto-fill
        // Shape: { classId: { feeTypeId: amount, ... }, ... }
        $classFeeMap = \App\Models\ClassFeeStructure::where('is_active', 1)
            ->get(['class_id', 'fee_type_id', 'amount'])
            ->groupBy('class_id')
            ->map(fn($rows) => $rows->pluck('amount', 'fee_type_id'))
            ->toArray();

        return view('fee_vouchers.create', compact(
            'classes',
            'students',
            'feeTypes',
            'preselectedStudentId',
            'preselectedPrevBalance',
            'preselectedOverdue',
            'classFeeMap'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'student_id'     => 'required|exists:students,id',
            'period_from'    => 'required|date',
            'period_to'      => 'required|date|after_or_equal:period_from',
            'payable_amount' => 'required|numeric|min:0',
            'due_date'       => 'required|date',
        ]);

        $voucher = DB::transaction(function () use ($request) {
            $voucherNo = 'FV-' . date('YmdHis');

            $voucher = FeeVoucher::create([
                'voucher_no'      => $voucherNo,
                'student_id'      => $request->student_id,
                'voucher_type'    => $request->voucher_type ?? 'monthly',
                'period_from'     => $request->period_from,
                'period_to'       => $request->period_to,
                'total_amount'    => $request->total_amount ?? $request->payable_amount,
                'discount'        => $request->discount ?? 0,
                'payable_amount'  => $request->payable_amount,
                'paid_amount'     => 0,
                'balance_amount'  => $request->payable_amount,
                'amount_in_words' => $request->amount_in_words,
                'due_date'        => $request->due_date,
                'status'          => 'unpaid',
                'notes'           => $request->notes,
            ]);

            if ($request->fee_type_id) {
                foreach ($request->fee_type_id as $key => $feeTypeId) {

                    // <input type="month"> sends "YYYY-MM"; MySQL DATE needs "YYYY-MM-DD"
                    $monthValue = $request->month[$key] ?? null;
                    if ($monthValue && strlen($monthValue) === 7) {
                        $monthValue = $monthValue . '-01';
                    }

                    FeeVoucherItem::create([
                        'voucher_id'   => $voucher->id,
                        'fee_type_id'  => $feeTypeId,
                        'description'  => $request->description[$key] ?? null,
                        'month'        => $monthValue,
                        'months_count' => $request->months_count[$key] ?? 1,
                        'amount'       => $request->amount[$key],
                    ]);
                }
            }

            // If "include previous balance" was checked, add it as a line item
            if ($request->include_previous_balance && $request->previous_balance_amount > 0) {

                $feeType = FeeType::firstOrCreate(
                    ['name' => 'Previous Balance'],
                    ['category' => 'other', 'is_active' => 1, 'description' => 'Previous outstanding balance']
                );

                FeeVoucherItem::create([
                    'voucher_id'   => $voucher->id,
                    'fee_type_id'  => $feeType->id,
                    'description'  => 'Previous outstanding balance',
                    'month'        => $request->period_from,
                    'months_count' => 1,
                    'amount'       => $request->previous_balance_amount,
                ]);

                $newTotal = $voucher->payable_amount + $request->previous_balance_amount;
                $voucher->update([
                    'total_amount'   => $newTotal,
                    'payable_amount' => $newTotal,
                    'balance_amount' => $newTotal,
                ]);
            }

            return $voucher;
        });

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', 'Fee Voucher ' . $voucher->voucher_no . ' created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $voucher = FeeVoucher::with(['items', 'student', 'payments'])->findOrFail($id);

        if ($voucher->status === 'paid') {
            return redirect()
                ->route('fee-vouchers.index')
                ->with('error', 'This voucher is fully paid and can no longer be edited. Use "Fix Method" on that voucher to correct the payment method or reference number instead.');
        }

        // The edit view already has a complete "locked" mode built for
        // partially-paid vouchers (items/totals/student disabled, only
        // period/due-date/notes editable) — it was just never given this
        // flag, so it always rendered as if unpaid. Wiring it up here is
        // what actually fixes editing for partial vouchers.
        $hasPayments = $voucher->payments->count() > 0;

        $classes  = PaClass::orderBy('class_order')->get();
        $students = Student::with('enrollments.class')
            ->orderBy('student_name')
            ->get();
        $feeTypes = FeeType::where('is_active', 1)->get();

        // Build a class→feeType→unitRate lookup for JS auto-fill
        $classFeeMap = \App\Models\ClassFeeStructure::where('is_active', 1)
            ->get(['class_id', 'fee_type_id', 'amount'])
            ->groupBy('class_id')
            ->map(fn($rows) => $rows->pluck('amount', 'fee_type_id'))
            ->toArray();

        return view('fee_vouchers.edit', compact('voucher', 'classes', 'students', 'feeTypes', 'classFeeMap', 'hasPayments'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $voucher = FeeVoucher::with('payments')->findOrFail($id);

        if ($voucher->status === 'paid') {
            return redirect()
                ->route('fee-vouchers.index')
                ->with('error', 'This voucher is fully paid and can no longer be edited. Use "Fix Method" on that voucher to correct the payment method or reference number instead.');
        }

        $hasPayments = $voucher->payments->count() > 0;

        // Server-side guard mirrors what the edit view already locks in the
        // UI: once a payment has been recorded, the student, fee items,
        // and every money field are frozen so paid/balance stays accurate.
        // Only period, due date, and notes may change on a partial voucher.
        if ($hasPayments) {
            $request->validate([
                'period_from' => 'required|date',
                'period_to'   => 'required|date|after_or_equal:period_from',
                'due_date'    => 'required|date',
            ]);
        } else {
            $request->validate([
                'student_id'     => 'required|exists:students,id',
                'period_from'    => 'required|date',
                'period_to'      => 'required|date|after_or_equal:period_from',
                'payable_amount' => 'required|numeric|min:0',
                'due_date'       => 'required|date',
            ]);
        }

        DB::transaction(function () use ($request, $voucher, $hasPayments) {

            $updateData = [
                'period_from' => $request->period_from,
                'period_to'   => $request->period_to,
                'due_date'    => $request->due_date,
                'notes'       => $request->notes,
            ];

            if (!$hasPayments) {
                $updateData['student_id']      = $request->student_id;
                $updateData['total_amount']    = $request->total_amount;
                $updateData['discount']        = $request->discount ?? 0;
                $updateData['payable_amount']  = $request->payable_amount;
                $updateData['amount_in_words'] = $request->amount_in_words;
            }

            $voucher->update($updateData);

            // Items are only rebuilt when nothing has been paid yet. Once a
            // payment exists, the UI locks every item row, so there is
            // nothing to rebuild — and doing so would risk breaking the
            // link between what was actually paid for and the receipt.
            if (!$hasPayments) {
                FeeVoucherItem::where('voucher_id', $voucher->id)->delete();

                if ($request->fee_type_id) {
                    foreach ($request->fee_type_id as $key => $feeTypeId) {

                        // <input type="month"> sends "YYYY-MM"; MySQL DATE needs "YYYY-MM-DD"
                        $monthValue = $request->month[$key] ?? null;
                        if ($monthValue && strlen($monthValue) === 7) {
                            $monthValue = $monthValue . '-01';
                        }

                        FeeVoucherItem::create([
                            'voucher_id'   => $voucher->id,
                            'fee_type_id'  => $feeTypeId,
                            'description'  => $request->description[$key] ?? null,
                            'month'        => $monthValue,
                            'months_count' => $request->months_count[$key] ?? 1,
                            'amount'       => $request->amount[$key],
                        ]);
                    }
                }
            }

            // Single source of truth: recompute paid/balance/status from the
            // actual payments on file, instead of blindly setting
            // balance_amount = payable_amount (which used to silently wipe
            // out a partial voucher's paid amount whenever it was possible
            // to reach this code path).
            $voucher->recalculateBalance();
        });

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', 'Fee voucher updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print($id)
    {
        $voucher = FeeVoucher::with(['student', 'items.feeType', 'payments'])->findOrFail($id);

        $cutoff = Carbon::now()->startOfMonth()->toDateString();

        $previousBalance = FeeVoucher::where('student_id', $voucher->student_id)
            ->outstanding()
            ->where('due_date', '<', $cutoff)
            ->where('balance_amount', '>', 0)
            ->where('id', '!=', $voucher->id)
            ->sum('balance_amount');

        return view('fee_vouchers.print', compact('voucher', 'previousBalance'));
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    |
    | Used to remove duplicate / mistakenly-created vouchers. A voucher can
    | only be deleted if NO payment has ever been recorded against it — this
    | prevents deleting a voucher out from under a receipt/ledger entry that
    | references it. If payments exist, the user must reverse them first
    | from Payment History, then delete the voucher.
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $voucher = FeeVoucher::withCount('payments')->findOrFail($id);

        if ($voucher->payments_count > 0) {
            return redirect()
                ->route('fee-vouchers.index')
                ->with('error', 'Voucher ' . $voucher->voucher_no . ' has payment(s) recorded against it and cannot be deleted. Reverse the payment(s) from Payment History first, then delete the voucher.');
        }

        DB::transaction(function () use ($voucher) {
            FeeVoucherItem::where('voucher_id', $voucher->id)->delete();
            $voucher->delete();
        });

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', 'Fee Voucher ' . $voucher->voucher_no . ' deleted successfully.');
    }
}
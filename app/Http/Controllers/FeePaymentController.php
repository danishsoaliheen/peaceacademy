<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FeeVoucher;
use App\Models\FeePayment;
use App\Models\Student;
use App\Helpers\PaymentMethodHelper;

class FeePaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Payment Form
    |--------------------------------------------------------------------------
    */

    public function create(FeeVoucher $voucher)
    {
        $voucher->load(['student', 'items.feeType', 'payments']);

        $paymentMethods = PaymentMethodHelper::enabled();

        return view('fee_payments.create', compact('voucher', 'paymentMethods'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Payment
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'voucher_id'   => 'required|exists:fee_vouchers,id',
            'student_id'   => 'required|exists:students,id',
            'payment_date' => 'required|date|before_or_equal:today',
            'amount_paid'  => 'required|numeric|min:1',
        ]);

        $payment = DB::transaction(function () use ($request) {
            $voucher = FeeVoucher::lockForUpdate()->findOrFail($request->voucher_id);

            /*
            |----------------------------------------------------------------------
            | Atomic receipt number generation (prevents duplicates)
            |----------------------------------------------------------------------
            */
            $receiptNo = FeeVoucher::nextReceiptNo();

            /*
            |----------------------------------------------------------------------
            | Create payment record
            |----------------------------------------------------------------------
            */
            return FeePayment::create([
                'voucher_id'     => $voucher->id,
                'student_id'     => $request->student_id,
                'receipt_no'     => $receiptNo,
                'amount_paid'    => $request->amount_paid,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method ?? 'Cash',
                'reference_no'   => $request->reference_no,
                'received_by'    => auth()->check() ? auth()->user()->name : 'Admin',
                'notes'          => $request->notes,
            ]);
        });

        /*
        |----------------------------------------------------------------------
        | Recalculate voucher balance (single source of truth)
        |----------------------------------------------------------------------
        */
        $payment->voucher->recalculateBalance();

        /*
        |----------------------------------------------------------------------
        | Redirect to receipt or back to voucher list
        |----------------------------------------------------------------------
        */
        if ($request->has('print_receipt')) {
            return redirect()->route('fee-payments.receipt', $payment->id);
        }

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', "Payment of Rs. " . number_format($request->amount_paid, 0) . " recorded. Receipt: {$payment->receipt_no}");
    }

    /*
    |--------------------------------------------------------------------------
    | Payment History (all payments)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = FeePayment::with(['student', 'voucher'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->from_date) {
            $query->where('payment_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->where('payment_date', '<=', $request->to_date);
        }

        /*
        |----------------------------------------------------------------------
        | FIX: Clone the query BEFORE pagination to get correct total.
        | Previously $query->sum() ran on the paginated query, returning
        | only the current page's sum instead of the full total.
        |----------------------------------------------------------------------
        */
        $totalReceived = (clone $query)->sum('amount_paid');

        $payments = $query->paginate(30)->withQueryString();

        $students = Student::where('is_active', 1)
            ->orderBy('student_name')
            ->get(['id', 'student_name', 'admission_no']);

        return view('fee_payments.index', compact('payments', 'totalReceived', 'students'));
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Payment (correct a wrong amount / method / date)
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $payment = FeePayment::with(['student', 'voucher'])->findOrFail($id);

        $paymentMethods = PaymentMethodHelper::enabled();

        // If the voucher is fully paid, only allow correcting payment method/reference/notes.
        // Amount and date are locked to avoid breaking the voucher balance.
        $methodOnlyMode = $payment->voucher->status === 'paid';

        return view('fee_payments.edit', compact('payment', 'paymentMethods', 'methodOnlyMode'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Payment
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $payment = FeePayment::findOrFail($id);
        $voucher = FeeVoucher::findOrFail($payment->voucher_id);

        $methodOnlyMode = $voucher->status === 'paid';

        if ($methodOnlyMode) {
            /*
            |------------------------------------------------------------------
            | Paid voucher — only payment method, reference & notes may change.
            | Amount and date are untouched so balance stays correct.
            |------------------------------------------------------------------
            */
            $request->validate([
                'payment_method' => 'required|string',
            ]);

            $payment->update([
                'payment_method' => $request->payment_method,
                'reference_no'   => $request->reference_no,
                'notes'          => $request->notes,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Payment method updated successfully.');
        }

        /*
        |----------------------------------------------------------------------
        | Normal edit (unpaid / partial voucher)
        |----------------------------------------------------------------------
        */
        $request->validate([
            'payment_date' => 'required|date|before_or_equal:today',
            'amount_paid'  => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($payment, $request) {
            $payment->update([
                'amount_paid'    => $request->amount_paid,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method ?? $payment->payment_method,
                'reference_no'   => $request->reference_no,
                'notes'          => $request->notes,
            ]);

            $payment->voucher->recalculateBalance();
        });

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', 'Payment updated. Voucher balance recalculated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Print Receipt
    |--------------------------------------------------------------------------
    */

    public function receipt($paymentId)
    {
        $payment = FeePayment::with(['student', 'voucher.items.feeType'])->findOrFail($paymentId);

        return view('fee_payments.receipt', compact('payment'));
    }

    /*
    |--------------------------------------------------------------------------
    | Delete / Reverse Payment
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $payment = FeePayment::findOrFail($id);

        DB::transaction(function () use ($payment) {
            $voucherId = $payment->voucher_id;
            $payment->delete();
            FeeVoucher::findOrFail($voucherId)->recalculateBalance();
        });

        return redirect()->back()->with('success', 'Payment reversed successfully.');
    }
}
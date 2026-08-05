<?php
// Save as: app/Http/Controllers/FeePaymentController.php

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
    | Sortable columns whitelist
    |--------------------------------------------------------------------------
    | Table names are explicit (fee_payments./students./fee_vouchers.) because
    | the index/export queries left-join students and fee_vouchers to allow
    | sorting by student name / voucher number.
    |--------------------------------------------------------------------------
    */

    private const SORTABLE_COLUMNS = [
        'id'             => 'fee_payments.id',
        'receipt_no'     => 'fee_payments.receipt_no',
        'student'        => 'students.student_name',
        'voucher'        => 'fee_vouchers.voucher_no',
        'amount_paid'    => 'fee_payments.amount_paid',
        'payment_date'   => 'fee_payments.payment_date',
        'payment_method' => 'fee_payments.payment_method',
        'received_by'    => 'fee_payments.received_by',
    ];

    private const SORT_DEFAULT_DIRECTIONS = [
        'id'             => 'desc',
        'receipt_no'     => 'desc',
        'student'        => 'asc',
        'voucher'        => 'asc',
        'amount_paid'    => 'desc',
        'payment_date'   => 'desc',
        'payment_method' => 'asc',
        'received_by'    => 'asc',
    ];

    private function resolveSort(Request $request): array
    {
        $sort = $request->string('sort')->toString();
        if ($sort === '' || !array_key_exists($sort, self::SORTABLE_COLUMNS)) {
            $sort = 'payment_date';
        }

        $direction = strtolower($request->string('direction')->toString());
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = self::SORT_DEFAULT_DIRECTIONS[$sort];
        }

        return [$sort, $direction];
    }

    /**
     * Shared filtered + joined query for index() and export().
     *
     * Date range defaults to the CURRENT MONTH unless the user explicitly
     * supplied from_date / to_date — this is the new default behaviour
     * requested (previously it showed all-time history with no default
     * window at all).
     *
     * Returns [query, fromDate, toDate] so both callers stay in sync.
     */
    private function buildFilteredQuery(Request $request): array
    {
        $fromDate = $request->filled('from_date')
            ? $request->from_date
            : now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->filled('to_date')
            ? $request->to_date
            : now()->endOfMonth()->format('Y-m-d');

        $query = FeePayment::query()
            ->leftJoin('students', 'students.id', '=', 'fee_payments.student_id')
            ->leftJoin('fee_vouchers', 'fee_vouchers.id', '=', 'fee_payments.voucher_id')
            ->select('fee_payments.*')
            ->with(['student', 'voucher'])
            ->whereBetween('fee_payments.payment_date', [$fromDate, $toDate]);

        if ($request->filled('student_id')) {
            $query->where('fee_payments.student_id', $request->student_id);
        }

        return [$query, $fromDate, $toDate];
    }

    private function applySort($query, string $sort, string $direction)
    {
        $column = self::SORTABLE_COLUMNS[$sort];

        $query->orderBy($column, $direction);

        // Stable secondary sort so rows with equal values (e.g. same date)
        // don't jump around between page loads.
        if ($sort !== 'payment_date') {
            $query->orderBy('fee_payments.payment_date', 'desc');
        }
        $query->orderBy('fee_payments.id', 'desc');

        return $query;
    }

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

            $receiptNo = FeeVoucher::nextReceiptNo();

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

        $payment->voucher->recalculateBalance();

        if ($request->has('print_receipt')) {
            return redirect()->route('fee-payments.receipt', $payment->id);
        }

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', "Payment of Rs. " . number_format($request->amount_paid, 0) . " recorded. Receipt: {$payment->receipt_no}");
    }

    /*
    |--------------------------------------------------------------------------
    | Payment History (all payments) — defaults to current month
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        [$sort, $direction]        = $this->resolveSort($request);
        [$query, $fromDate, $toDate] = $this->buildFilteredQuery($request);

        // Total for the header card must reflect the FULL filtered set,
        // not just the current page — clone before pagination.
        $totalReceived = (clone $query)->sum('fee_payments.amount_paid');

        $this->applySort($query, $sort, $direction);

        $payments = $query->paginate(30)->withQueryString();

        $students = Student::where('is_active', 1)
            ->orderBy('student_name')
            ->get(['id', 'student_name', 'admission_no']);

        return view('fee_payments.index', compact(
            'payments', 'totalReceived', 'students',
            'sort', 'direction', 'fromDate', 'toDate'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Export — same filters/sort as index(), no pagination.
    | Streams an HTML table with .xls headers (opens directly in Excel),
    | same lightweight approach as student_ledger/export.blade.php.
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        [$sort, $direction]          = $this->resolveSort($request);
        [$query, $fromDate, $toDate] = $this->buildFilteredQuery($request);

        $this->applySort($query, $sort, $direction);

        $payments      = $query->get();
        $totalReceived = $payments->sum('amount_paid');

        $filename = 'payment-history_' . date('Y-m-d_His') . '.xls';

        return response()
            ->view('fee_payments.export', compact('payments', 'totalReceived', 'fromDate', 'toDate'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
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

        $methodOnlyMode = in_array($payment->voucher->status, ['paid', 'carried_forward'], true);

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

        $methodOnlyMode = in_array($voucher->status, ['paid', 'carried_forward'], true);

        if ($methodOnlyMode) {
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
    | Print / Download Receipt
    |--------------------------------------------------------------------------
    | ?download=1 switches the view from auto-print to auto-download-as-PDF
    | (via html2pdf.js, same library already used on the student profile
    | page). This is the "Send" action from the payment history list — for
    | now it just saves the PDF locally; once WhatsApp is wired up this same
    | generated file becomes what gets sent to the student's WhatsApp number.
    |--------------------------------------------------------------------------
    */

    public function receipt(Request $request, $id)
    {
        $payment  = FeePayment::with(['student', 'voucher.items.feeType'])->findOrFail($id);
        $download = $request->boolean('download');

        return view('fee_payments.receipt', compact('payment', 'download'));
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
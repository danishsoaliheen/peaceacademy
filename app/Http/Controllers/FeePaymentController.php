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

    /*
    |--------------------------------------------------------------------------
    | Resolve sorting
    |--------------------------------------------------------------------------
    */

    private function resolveSort(Request $request): array
    {
        $sort = $request->string('sort')->toString();

        if ($sort === '' || !array_key_exists($sort, self::SORTABLE_COLUMNS)) {
            $sort = 'payment_date';
        }

        $direction = strtolower(
            $request->string('direction')->toString()
        );

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = self::SORT_DEFAULT_DIRECTIONS[$sort];
        }

        return [$sort, $direction];
    }

    /*
    |--------------------------------------------------------------------------
    | Shared filtered + joined query
    |--------------------------------------------------------------------------
    |
    | Used by both index() and export().
    |
    | Family Code filtering intentionally does NOT use is_active.
    | Historical payments for inactive students remain available.
    |--------------------------------------------------------------------------
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
            ->leftJoin(
                'students',
                'students.id',
                '=',
                'fee_payments.student_id'
            )
            ->leftJoin(
                'fee_vouchers',
                'fee_vouchers.id',
                '=',
                'fee_payments.voucher_id'
            )
            ->select('fee_payments.*')
            ->with(['student', 'voucher'])
            ->whereBetween(
                'fee_payments.payment_date',
                [$fromDate, $toDate]
            );

        /*
        |--------------------------------------------------------------------------
        | Student filter
        |--------------------------------------------------------------------------
        |
        | No is_active restriction here.
        | Historical payments for inactive students remain accessible.
        |
        */

        if ($request->filled('student_id')) {
            $query->where(
                'fee_payments.student_id',
                $request->student_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Family Code filter
        |--------------------------------------------------------------------------
        |
        | This deliberately includes ACTIVE and INACTIVE students.
        |
        */

        if ($request->filled('family_code')) {
            $query->where(
                'students.family_code',
                $request->family_code
            );
        }

        return [$query, $fromDate, $toDate];
    }

    /*
    |--------------------------------------------------------------------------
    | Apply sorting
    |--------------------------------------------------------------------------
    */

    private function applySort($query, string $sort, string $direction)
    {
        $column = self::SORTABLE_COLUMNS[$sort];

        $query->orderBy($column, $direction);

        /*
        | Stable secondary sort.
        */
        if ($sort !== 'payment_date') {
            $query->orderBy(
                'fee_payments.payment_date',
                'desc'
            );
        }

        $query->orderBy(
            'fee_payments.id',
            'desc'
        );

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Show Payment Form
    |--------------------------------------------------------------------------
    */

    public function create(FeeVoucher $voucher)
    {
        $voucher->load([
            'student',
            'items.feeType',
            'payments'
        ]);

        $paymentMethods = PaymentMethodHelper::enabled();

        return view(
            'fee_payments.create',
            compact('voucher', 'paymentMethods')
        );
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

            $voucher = FeeVoucher::lockForUpdate()
                ->findOrFail($request->voucher_id);

            $receiptNo = FeeVoucher::nextReceiptNo();

            return FeePayment::create([
                'voucher_id'     => $voucher->id,
                'student_id'     => $request->student_id,
                'receipt_no'     => $receiptNo,
                'amount_paid'    => $request->amount_paid,
                'payment_date'   => $request->payment_date,
                'payment_method' => $request->payment_method ?? 'Cash',
                'reference_no'   => $request->reference_no,
                'received_by'    => auth()->check()
                    ? auth()->user()->name
                    : 'Admin',
                'notes'          => $request->notes,
            ]);
        });

        $payment->voucher->recalculateBalance();

        if ($request->has('print_receipt')) {
            return redirect()->route(
                'fee-payments.receipt',
                $payment->id
            );
        }

        return redirect()
            ->route('fee-vouchers.index')
            ->with(
                'success',
                "Payment of Rs. " .
                number_format($request->amount_paid, 0) .
                " recorded. Receipt: {$payment->receipt_no}"
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Payment History
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        [$sort, $direction] =
            $this->resolveSort($request);

        [$query, $fromDate, $toDate] =
            $this->buildFilteredQuery($request);

        /*
        |--------------------------------------------------------------------------
        | Total received from the complete filtered result
        |--------------------------------------------------------------------------
        */

        $totalReceived =
            (clone $query)->sum(
                'fee_payments.amount_paid'
            );

        $this->applySort(
            $query,
            $sort,
            $direction
        );

        $payments =
            $query
                ->paginate(30)
                ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Student dropdown
        |--------------------------------------------------------------------------
        |
        | Show ACTIVE + INACTIVE students.
        | Inactive students are clearly marked.
        |
        */

        $students = Student::query()
            ->orderBy('student_name')
            ->get([
                'id',
                'student_name',
                'admission_no',
                'family_code',
                'is_active',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Family Code dropdown
        |--------------------------------------------------------------------------
        |
        | Unique family codes only.
        | Inactive students are included.
        |
        */

        $familyCodes = Student::query()
            ->whereNotNull('family_code')
            ->where('family_code', '!=', '')
            ->select('family_code')
            ->distinct()
            ->orderBy('family_code')
            ->pluck('family_code');

        return view(
            'fee_payments.index',
            compact(
                'payments',
                'totalReceived',
                'students',
                'familyCodes',
                'sort',
                'direction',
                'fromDate',
                'toDate'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Export Payment History as CSV
    |--------------------------------------------------------------------------
    |
    | Exports the same filtered payment history shown on the screen.
    | Includes ACTIVE + INACTIVE students.
    |
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        [$sort, $direction] =
            $this->resolveSort($request);

        [$query, $fromDate, $toDate] =
            $this->buildFilteredQuery($request);

        $this->applySort(
            $query,
            $sort,
            $direction
        );

        $payments = $query->get();

        $totalReceived =
            $payments->sum('amount_paid');

        /*
        |--------------------------------------------------------------------------
        | Selected Family Code
        |--------------------------------------------------------------------------
        */

        $familyCode = $request->filled('family_code')
            ? $request->family_code
            : null;

        /*
        |--------------------------------------------------------------------------
        | CSV filename
        |--------------------------------------------------------------------------
        */

        $filename = 'payment-history';

        if ($familyCode) {
            $filename .= '-family-' .
                preg_replace(
                    '/[^A-Za-z0-9_-]/',
                    '-',
                    $familyCode
                );
        }

        $filename .= '-' .
            date('Y-m-d_His') .
            '.csv';

        /*
        |--------------------------------------------------------------------------
        | Generate CSV
        |--------------------------------------------------------------------------
        */

        return response()->streamDownload(
            function () use (
                $payments,
                $totalReceived,
                $fromDate,
                $toDate,
                $familyCode
            ) {

                $handle = fopen(
                    'php://output',
                    'w'
                );

                /*
                |--------------------------------------------------------------------------
                | UTF-8 BOM
                |--------------------------------------------------------------------------
                |
                | Helps Microsoft Excel correctly read UTF-8
                | characters, including Urdu/special characters.
                |
                */

                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                /*
                |--------------------------------------------------------------------------
                | Report heading
                |--------------------------------------------------------------------------
                */

                fputcsv(
                    $handle,
                    ['Peace Academy - Payment History']
                );

                fputcsv(
                    $handle,
                    [
                        'Payment Period',
                        $fromDate . ' to ' . $toDate
                    ]
                );

                if ($familyCode) {
                    fputcsv(
                        $handle,
                        [
                            'Family Code',
                            $familyCode
                        ]
                    );

                    fputcsv(
                        $handle,
                        [
                            'Note',
                            'Includes active and inactive students'
                        ]
                    );
                }

                fputcsv(
                    $handle,
                    []
                );

                /*
                |--------------------------------------------------------------------------
                | Column headings
                |--------------------------------------------------------------------------
                */

                fputcsv(
                    $handle,
                    [
                        'Sr#',
                        'Receipt No',
                        'Student',
                        'Family Code',
                        'Admission No',
                        'Voucher No',
                        'Amount Paid (Rs.)',
                        'Payment Date',
                        'Payment Method',
                        'Received By',
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Payment rows
                |--------------------------------------------------------------------------
                */

                foreach (
                    $payments as $index => $payment
                ) {

                    $student =
                        $payment->student;

                    $studentName =
                        $student?->student_name
                        ?? 'Unknown Student';

                    /*
                    | Mark inactive students clearly.
                    */

                    if (
                        $student &&
                        !$student->is_active
                    ) {
                        $studentName .=
                            ' (Inactive)';
                    }

                    fputcsv(
                        $handle,
                        [
                            $index + 1,
                            $payment->receipt_no,
                            $studentName,
                            $student?->family_code ?? '',
                            $student?->admission_no ?? '',
                            $payment->voucher?->voucher_no ?? '',
                            number_format(
                                (float) $payment->amount_paid,
                                2,
                                '.',
                                ''
                            ),
                            $payment->payment_date,
                            $payment->payment_method,
                            $payment->received_by,
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Total row
                |--------------------------------------------------------------------------
                */

                fputcsv(
                    $handle,
                    []
                );

                fputcsv(
                    $handle,
                    [
                        '',
                        '',
                        'TOTAL RECEIVED',
                        '',
                        '',
                        '',
                        number_format(
                            (float) $totalReceived,
                            2,
                            '.',
                            ''
                        ),
                        '',
                        '',
                        '',
                    ]
                );

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',

                'Cache-Control' =>
                    'no-store, no-cache',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Payment
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $payment =
            FeePayment::with([
                'student',
                'voucher'
            ])->findOrFail($id);

        $paymentMethods =
            PaymentMethodHelper::enabled();

        $methodOnlyMode =
            in_array(
                $payment->voucher->status,
                ['paid', 'carried_forward'],
                true
            );

        return view(
            'fee_payments.edit',
            compact(
                'payment',
                'paymentMethods',
                'methodOnlyMode'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Payment
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $payment =
            FeePayment::findOrFail($id);

        $voucher =
            FeeVoucher::findOrFail(
                $payment->voucher_id
            );

        $methodOnlyMode =
            in_array(
                $voucher->status,
                ['paid', 'carried_forward'],
                true
            );

        if ($methodOnlyMode) {

            $request->validate([
                'payment_method' =>
                    'required|string',
            ]);

            $payment->update([
                'payment_method' =>
                    $request->payment_method,

                'reference_no' =>
                    $request->reference_no,

                'notes' =>
                    $request->notes,
            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Payment method updated successfully.'
                );
        }

        $request->validate([
            'payment_date' =>
                'required|date|before_or_equal:today',

            'amount_paid' =>
                'required|numeric|min:1',
        ]);

        DB::transaction(
            function () use (
                $payment,
                $request
            ) {

                $payment->update([
                    'amount_paid' =>
                        $request->amount_paid,

                    'payment_date' =>
                        $request->payment_date,

                    'payment_method' =>
                        $request->payment_method
                            ?? $payment->payment_method,

                    'reference_no' =>
                        $request->reference_no,

                    'notes' =>
                        $request->notes,
                ]);

                $payment->voucher
                    ->recalculateBalance();
            }
        );

        return redirect()
            ->route('fee-vouchers.index')
            ->with(
                'success',
                'Payment updated. Voucher balance recalculated.'
            );
    }

/*
|--------------------------------------------------------------------------
| WhatsApp Payment Acknowledgement
|--------------------------------------------------------------------------
|
| Opens WhatsApp with a pre-filled payment acknowledgement.
| No payment receipt PDF is generated or attached.
|
*/

public function whatsapp($id)
{
    $payment = FeePayment::with([
        'student',
        'voucher',
    ])->findOrFail($id);

    $student = $payment->student;
    $voucher = $payment->voucher;

    /*
    |--------------------------------------------------------------------------
    | Student Check
    |--------------------------------------------------------------------------
    */

    if (!$student) {
        return back()->with(
            'error',
            'Student record not found.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Voucher Check
    |--------------------------------------------------------------------------
    */

    if (!$voucher) {
        return back()->with(
            'error',
            'Fee voucher record not found.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Number
    |--------------------------------------------------------------------------
    |
    | Priority:
    | 1. Student WhatsApp
    | 2. Mother's WhatsApp
    |
    */

    $whatsappNumber =
        $student->whatsapp_no
        ?: $student->mother_whatsapp_no;

    /*
    |--------------------------------------------------------------------------
    | No WhatsApp Number
    |--------------------------------------------------------------------------
    */

    if (!$whatsappNumber) {
        return back()->with(
            'error',
            'WhatsApp number not updated in Student Profile.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Pakistan WhatsApp Number
    |--------------------------------------------------------------------------
    */

    $whatsappNumber = preg_replace(
        '/\D+/',
        '',
        $whatsappNumber
    );

    if (str_starts_with($whatsappNumber, '0')) {
        $whatsappNumber =
            '92' . substr($whatsappNumber, 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Class Name
    |--------------------------------------------------------------------------
    */

    $student->loadMissing(
        'enrollments.class'
    );

    $className =
        $student->activeEnrollment?->class?->class_name
        ?? 'N/A';

    /*
    |--------------------------------------------------------------------------
    | Refresh Voucher Balance
    |--------------------------------------------------------------------------
    |
    | Make sure we use the balance AFTER this payment.
    |
    */

    $voucher->recalculateBalance();

    $voucher->refresh();

    /*
    |--------------------------------------------------------------------------
    | Student Name
    |--------------------------------------------------------------------------
    */

    $studentName =
        $student->student_name
        ?: 'Student';

    /*
    |--------------------------------------------------------------------------
    | Payment Date
    |--------------------------------------------------------------------------
    */

    $paymentDate =
        date(
            'd F Y',
            strtotime($payment->payment_date)
        );

    /*
    |--------------------------------------------------------------------------
    | Amount Received
    |--------------------------------------------------------------------------
    */

    $amountReceived =
        number_format(
            (float) $payment->amount_paid,
            0
        );

    /*
    |--------------------------------------------------------------------------
    | Remaining Balance
    |--------------------------------------------------------------------------
    */

    $remainingBalance =
        number_format(
            max(
                0,
                (float) $voucher->balance_amount
            ),
            0
        );

    /*
    |--------------------------------------------------------------------------
    | Payment Status
    |--------------------------------------------------------------------------
    */

    $status =
        (float) $voucher->balance_amount <= 0
            ? 'Paid in Full'
            : 'Partially Paid';

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Message
    |--------------------------------------------------------------------------
    */

    $message =
        "Assalam-o-Alaikum,\n\n"
        . "Rs. {$amountReceived}/- {$payment->payment_method} received for {$studentName} of {$className} on {$paymentDate}.\n"
        . "against Voucher No: {$voucher->voucher_no}\n"
        . "Receipt No: {$payment->receipt_no}\n"
        . "Remaining Balance: Rs. {$remainingBalance}/-\n"
        . "Status: {$status}\n\n"
        . "Thank you.\n"
        . "PEACE ACADEMY";

    /*
    |--------------------------------------------------------------------------
    | Open WhatsApp With Pre-filled Message
    |--------------------------------------------------------------------------
    */

    $whatsappUrl =
        'https://wa.me/'
        . $whatsappNumber
        . '?text='
        . urlencode($message);

    return redirect()->away(
        $whatsappUrl
    );
}

    /*
    |--------------------------------------------------------------------------
    | Print / Download Receipt
    |--------------------------------------------------------------------------
    */

    public function receipt(
        Request $request,
        $id
    ) {
        $payment =
            FeePayment::with([
                'student',
                'voucher.items.feeType'
            ])->findOrFail($id);

        $download =
            $request->boolean('download');

        return view(
            'fee_payments.receipt',
            compact(
                'payment',
                'download'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete / Reverse Payment
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $payment =
            FeePayment::findOrFail($id);

        DB::transaction(
            function () use ($payment) {

                $voucherId =
                    $payment->voucher_id;

                $payment->delete();

                FeeVoucher::findOrFail(
                    $voucherId
                )->recalculateBalance();
            }
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Payment reversed successfully.'
            );
    }
}
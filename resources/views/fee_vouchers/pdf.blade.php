<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Fee Voucher - {{ $voucher->voucher_no }}
    </title>

    <style>

        @page {
            size: A4;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #222;
            background: #fff;
        }

        .voucher {
            width: 100%;
            margin: 0 auto;
            padding: 18px;
            border: 1px solid #333;
        }

        /* ---------------------------------------------------------
           HEADER
        --------------------------------------------------------- */

        .header {
            width: 100%;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 18%;
            vertical-align: middle;
        }

        .logo {
            max-width: 85px;
            max-height: 85px;
        }

        .school-cell {
            width: 62%;
            text-align: center;
            vertical-align: middle;
        }

        .school-name {
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .school-subtitle {
            font-size: 11px;
            color: #555;
        }

        .voucher-cell {
            width: 20%;
            text-align: right;
            vertical-align: middle;
        }

        .voucher-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .voucher-number {
            font-size: 11px;
            font-weight: bold;
        }

        /* ---------------------------------------------------------
           STATUS
        --------------------------------------------------------- */

        .status-wrapper {
            text-align: center;
            margin-bottom: 12px;
        }

        .status {
            display: inline-block;
            padding: 5px 14px;
            border: 1px solid #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        /* ---------------------------------------------------------
           INFORMATION
        --------------------------------------------------------- */

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .info-table td {
            border: 1px solid #ccc;
            padding: 7px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #555;
            width: 18%;
        }

        .value {
            width: 32%;
        }

        /* ---------------------------------------------------------
           FEE ITEMS
        --------------------------------------------------------- */

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 14px;
        }

        .items-table th {
            background: #eeeeee;
            border: 1px solid #999;
            padding: 7px;
            text-align: left;
            font-size: 10px;
        }

        .items-table td {
            border: 1px solid #ccc;
            padding: 7px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* ---------------------------------------------------------
           TOTALS
        --------------------------------------------------------- */

        .totals-wrapper {
            width: 100%;
            margin-top: 8px;
        }

        .totals-table {
            width: 55%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals-table td {
            border: 1px solid #ccc;
            padding: 7px;
        }

        .total-label {
            font-weight: bold;
            text-align: right;
        }

        .grand-total td {
            font-weight: bold;
            font-size: 13px;
            background: #eeeeee;
            border-top: 2px solid #333;
        }

        /* ---------------------------------------------------------
           AMOUNT IN WORDS
        --------------------------------------------------------- */

        .amount-words {
            margin-top: 12px;
            padding: 8px;
            border: 1px solid #ccc;
        }

        .amount-words-label {
            font-weight: bold;
        }

        /* ---------------------------------------------------------
           PREVIOUS BALANCE
        --------------------------------------------------------- */

        .previous-balance {
            margin-top: 12px;
            padding: 9px;
            border: 1px solid #999;
            background: #f5f5f5;
        }

        .previous-balance-title {
            font-weight: bold;
            margin-bottom: 4px;
        }

        /* ---------------------------------------------------------
           PAYMENT HISTORY
        --------------------------------------------------------- */

        .section-title {
            margin-top: 15px;
            margin-bottom: 7px;
            padding-bottom: 4px;
            border-bottom: 1px solid #333;
            font-size: 12px;
            font-weight: bold;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .payment-table th {
            background: #eeeeee;
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        .payment-table td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        /* ---------------------------------------------------------
           NOTES
        --------------------------------------------------------- */

        .notes {
            margin-top: 12px;
            padding: 8px;
            border: 1px solid #ccc;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 4px;
        }

        /* ---------------------------------------------------------
           UNPAID NOTICE
        --------------------------------------------------------- */

        .unpaid-notice {
            margin-top: 12px;
            padding: 9px;
            border: 1px solid #999;
            text-align: center;
            font-weight: bold;
        }

        /* ---------------------------------------------------------
           FOOTER
        --------------------------------------------------------- */

        .footer {
            margin-top: 22px;
            padding-top: 8px;
            border-top: 1px solid #999;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .generated {
            margin-top: 4px;
            font-size: 8px;
            color: #888;
        }

    </style>

</head>


<body>

<div class="voucher">


    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="header">

        <table class="header-table">

            <tr>

                <td class="logo-cell">

                    @if(!empty($logoData))

                        <img src="{{ $logoData }}"
                             class="logo"
                             alt="School Logo">

                    @endif

                </td>


                <td class="school-cell">

                    <div class="school-name">
                        PEACE ACADEMY
                    </div>

                    <div class="school-subtitle">
                        Fee Voucher
                    </div>

                </td>


                <td class="voucher-cell">

                    <div class="voucher-title">
                        FEE VOUCHER
                    </div>

                    <div class="voucher-number">
                        {{ $voucher->voucher_no }}
                    </div>

                </td>

            </tr>

        </table>

    </div>


    {{-- =========================================================
         STATUS
    ========================================================== --}}

    <div class="status-wrapper">

        <span class="status">
            {{ strtoupper($voucher->status) }}
        </span>

    </div>


    {{-- =========================================================
         STUDENT / VOUCHER INFORMATION
    ========================================================== --}}

    <table class="info-table">

        <tr>

            <td class="label">
                Student Name
            </td>

            <td class="value">
                {{ strtoupper($voucher->student?->student_name ?? '—') }}
            </td>

            <td class="label">
                Admission No.
            </td>

            <td class="value">
                {{ $voucher->student?->admission_no ?? '—' }}
            </td>

        </tr>


        <tr>

            <td class="label">
                Class
            </td>

            <td class="value">
                {{ $voucher->student?->activeEnrollment?->class?->class_name ?? '—' }}
            </td>

            <td class="label">
                Family Code
            </td>

            <td class="value">
                {{ $voucher->student?->family_code ?? '—' }}
            </td>

        </tr>


        <tr>

            <td class="label">
                Voucher Period
            </td>

            <td class="value">

                @if($voucher->period_from)

                    {{ \Carbon\Carbon::parse($voucher->period_from)->format('d-M-Y') }}

                    @if($voucher->period_to)

                        -
                        {{ \Carbon\Carbon::parse($voucher->period_to)->format('d-M-Y') }}

                    @endif

                @else

                    —

                @endif

            </td>


            <td class="label">
                Due Date
            </td>

            <td class="value">

                @if($voucher->due_date)

                    {{ strtoupper(\Carbon\Carbon::parse($voucher->due_date)->format('d-M-Y')) }}

                @else

                    —

                @endif

            </td>

        </tr>

    </table>


    {{-- =========================================================
         FEE ITEMS
    ========================================================== --}}

    <div class="section-title">
        Fee Details
    </div>


    <table class="items-table">

        <thead>

            <tr>

                <th width="6%">
                    #
                </th>

                <th width="28%">
                    Fee Type
                </th>

                <th width="36%">
                    Description
                </th>

                <th width="10%" class="text-center">
                    Months
                </th>

                <th width="20%" class="text-right">
                    Amount
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($voucher->items as $index => $item)

                <tr>

                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $item->feeType?->name ?? '—' }}
                    </td>

                    <td>
                        {{ $item->description ?? '—' }}
                    </td>

                    <td class="text-center">
                        {{ $item->months_count ?? 1 }}
                    </td>

                    <td class="text-right">
                        {{ number_format((float) $item->amount, 0) }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5"
                        class="text-center">

                        No fee items found.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- =========================================================
         TOTALS
    ========================================================== --}}

    <div class="totals-wrapper">

        <table class="totals-table">

            <tr>

                <td class="total-label">
                    Total Amount
                </td>

                <td class="text-right">
                    {{ number_format((float) $voucher->total_amount, 0) }}
                </td>

            </tr>


            <tr>

                <td class="total-label">
                    Discount
                </td>

                <td class="text-right">
                    {{ number_format((float) $voucher->discount, 0) }}
                </td>

            </tr>


            <tr>

                <td class="total-label">
                    Payable Amount
                </td>

                <td class="text-right">
                    {{ number_format((float) $voucher->payable_amount, 0) }}
                </td>

            </tr>


            <tr>

                <td class="total-label">
                    Paid Amount
                </td>

                <td class="text-right">
                    {{ number_format((float) $voucher->paid_amount, 0) }}
                </td>

            </tr>


            <tr class="grand-total">

                <td class="total-label">
                    Balance
                </td>

                <td class="text-right">

                    {{ number_format((float) $voucher->balance_amount, 0) }}

                </td>

            </tr>

        </table>

    </div>


    {{-- =========================================================
         AMOUNT IN WORDS
    ========================================================== --}}

    @if(!empty($voucher->amount_in_words))

        <div class="amount-words">

            <span class="amount-words-label">
                Amount in Words:
            </span>

            {{ $voucher->amount_in_words }}

        </div>

    @endif


    {{-- =========================================================
         PREVIOUS BALANCE
    ========================================================== --}}

    @if((float) $previousBalance > 0)

        <div class="previous-balance">

            <div class="previous-balance-title">
                Previous Outstanding Balance
            </div>

            Previous outstanding balance:
            <strong>
                {{ number_format((float) $previousBalance, 0) }}
            </strong>

        </div>

    @endif


    {{-- =========================================================
         PAYMENT HISTORY
    ========================================================== --}}

    @if($voucher->payments && $voucher->payments->count() > 0)

        <div class="section-title">
            Payment History
        </div>


        <table class="payment-table">

            <thead>

                <tr>

                    <th>
                        Date
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Method
                    </th>

                    <th>
                        Reference
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach($voucher->payments as $payment)

                    <tr>

                        <td>

                            @if($payment->payment_date)

                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d-M-Y') }}

                            @elseif($payment->created_at)

                                {{ \Carbon\Carbon::parse($payment->created_at)->format('d-M-Y') }}

                            @else

                                —

                            @endif

                        </td>


                        <td class="text-right">

                            {{ number_format((float) ($payment->amount ?? 0), 0) }}

                        </td>


                        <td>

                            {{ $payment->payment_method ?? '—' }}

                        </td>


                        <td>

                            {{ $payment->reference_no
                                ?? $payment->reference
                                ?? '—' }}

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @endif


    {{-- =========================================================
         NOTES
    ========================================================== --}}

    @if(!empty($voucher->notes))

        <div class="notes">

            <div class="notes-title">
                Notes
            </div>

            {{ $voucher->notes }}

        </div>

    @endif


    {{-- =========================================================
         UNPAID NOTICE
    ========================================================== --}}

    @if(
        strtolower($voucher->status) !== 'paid'
        && (float) $voucher->balance_amount > 0
    )

        <div class="unpaid-notice">

            Please clear the outstanding balance by the due date.

        </div>

    @endif


    {{-- =========================================================
         FOOTER
    ========================================================== --}}

    <div class="footer">

        <div>
            Peace Academy
        </div>

        <div class="generated">

            Generated on
            {{ now()->setTimezone('Asia/Karachi')->format('d-M-Y h:i A') }}

        </div>

    </div>


</div>

</body>

</html>

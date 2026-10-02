```php
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        {{ strtoupper($voucher->student->student_name ?? '') }} — {{ $voucher->voucher_no ?? $voucher->id }}
    </title>

    <style>

        /* =========================================================
           PAGE & BASE
        ========================================================= */

        @page {
            size: A4;
            margin: 10mm;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11px;
            background: #ffffff;
            color: #000000;
        }

        .voucher {
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border: 1.5px solid #000000;
        }

        /* =========================================================
           HEADER
        ========================================================= */

        .header {
            width: 100%;
            border-bottom: 2px solid #000000;
            padding: 12px 16px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 12%;
            vertical-align: middle;
        }

        .logo {
            width: 56px;
            height: auto;
        }

        .school-cell {
            width: 88%;
            vertical-align: middle;
        }

        .school-name {
            font-size: 19px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .school-sub {
            font-size: 10px;
            margin-top: 2px;
        }

        /* =========================================================
           STATUS
        ========================================================= */

        .status-line {
            text-align: center;
            padding: 6px;
            font-size: 11px;
            font-weight: 700;
            border-bottom: 1px solid #000000;
            letter-spacing: 1px;
        }

        /* =========================================================
           BODY
        ========================================================= */

        .v-body {
            padding: 16px;
        }

        /* =========================================================
           TABLE BASE
        ========================================================= */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* =========================================================
           INFORMATION GRID
        ========================================================= */

        .info-grid {
            font-size: 11px;
        }

        .info-grid td {
            border: 1px solid #000000;
            padding: 5px 8px;
            vertical-align: middle;
        }

        .info-grid td.lbl {
            font-weight: 700;
            background: #eeeeee;
            width: 18%;
            white-space: nowrap;
        }

        .info-grid td.val {
            width: 32%;
        }

        /* =========================================================
           SECTION TITLES
        ========================================================= */

        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: #000000;
            color: #ffffff;
            padding: 5px 8px;
            margin-top: 14px;
        }

        /* =========================================================
           FEE ITEMS
        ========================================================= */

        .items-table {
            font-size: 10px;
        }

        .items-table th {
            border: 1px solid #000000;
            background: #eeeeee;
            padding: 6px 8px;
            font-size: 10px;
            text-align: left;
        }

        .items-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
            vertical-align: top;
        }

        /* =========================================================
           ALIGNMENT
        ========================================================= */

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .mono {
            font-family: "DejaVu Sans Mono", "Courier New", monospace;
        }

        /* =========================================================
           TOTALS
        ========================================================= */

        .totals-wrap {
            width: 100%;
            margin-top: 12px;
        }

        .totals-table {
            width: 300px;
            margin-left: auto;
            font-size: 11px;
        }

        .totals-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
        }

        .totals-table td.lbl {
            font-weight: 700;
            background: #eeeeee;
        }

        .totals-table td.val {
            text-align: right;
        }

        .row-payable td {
            background: #000000;
            color: #ffffff;
            font-weight: 700;
            font-size: 12px;
        }

        .row-paid td {
            font-weight: 700;
        }

        .row-balance td,
        .row-balance-zero td {
            font-weight: 700;
            border-top: 2px solid #000000;
        }

        /* =========================================================
           AMOUNT IN WORDS
        ========================================================= */

        .amount-words {
            margin-top: 14px;
            border: 1px solid #000000;
            padding: 8px 10px;
            font-size: 10px;
        }

        /* =========================================================
           PAYMENT HISTORY
        ========================================================= */

        .payment-table {
            margin-top: 0;
            font-size: 10px;
        }

        .payment-table th {
            border: 1px solid #000000;
            background: #eeeeee;
            padding: 5px 7px;
            font-size: 9.5px;
            text-align: left;
        }

        .payment-table td {
            border: 1px solid #000000;
            padding: 5px 7px;
            font-size: 10px;
            vertical-align: middle;
        }

        .payment-table tfoot td {
            font-weight: 700;
            background: #eeeeee;
        }

        /* =========================================================
           STATUS PILL
        ========================================================= */

        .pill {
            border: 1px solid #000000;
            padding: 2px 7px;
            font-size: 9px;
            font-weight: 700;
        }

        /* =========================================================
           NOTES
        ========================================================= */

        .notes-box {
            margin-top: 12px;
            border: 1px dashed #000000;
            padding: 7px 10px;
            font-size: 10px;
        }

        /* =========================================================
           UNPAID NOTICE
        ========================================================= */

        .flag-box {
            margin-top: 14px;
            padding: 8px 12px;
            border: 1px dashed #000000;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        /* =========================================================
           PREVIOUS BALANCE
        ========================================================= */

        .flag-box-left {
            margin-top: 10px;
            padding: 7px 10px;
            border: 1px solid #000000;
            font-size: 11px;
        }

        /* =========================================================
           FOOTER
        ========================================================= */

        .footer {
            margin-top: 26px;
            padding-top: 12px;
            border-top: 1px dashed #000000;
            font-size: 9px;
        }

    </style>
</head>

<body>

@php

    /*
    |--------------------------------------------------------------------------
    | Voucher Calculations
    |--------------------------------------------------------------------------
    */

    $status = strtolower($voucher->status ?? 'unpaid');

    $paidAmount = (float) ($voucher->paid_amount ?? 0);

    $balanceAmount = (float) (
        $voucher->balance_amount
        ?? $voucher->payable_amount
        ?? 0
    );

    /*
    |--------------------------------------------------------------------------
    | Payment History
    |--------------------------------------------------------------------------
    */

    $payments = $voucher->payments ?? collect();

    $hasPayments = $payments->count() > 0;

    /*
    |--------------------------------------------------------------------------
    | Status Label
    |--------------------------------------------------------------------------
    */

    $statusLabel = match ($status) {
        'paid' => '*** FULLY PAID ***',

        'partial' => '*** PARTIALLY PAID — BALANCE DUE ***',

        default => '*** UNPAID — PAYMENT DUE ***',
    };

@endphp


<div class="voucher">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="header">

        <table class="header-table">

            <tr>

                <td class="logo-cell">

                    @if(!empty($logoData))

                        <img
                            class="logo"
                            src="{{ $logoData }}"
                            alt="Peace Academy Logo"
                        >

                    @endif

                </td>

                <td class="school-cell">

                    <div class="school-name">
                        PEACE ACADEMY
                    </div>

                    <div class="school-sub">
                        Fee Management System
                    </div>

                </td>

            </tr>

        </table>

    </div>


    {{-- =========================================================
         STATUS
    ========================================================== --}}

    <div class="status-line">
        {{ $statusLabel }}
    </div>


    <div class="v-body">


        {{-- =====================================================
             STUDENT / VOUCHER INFORMATION
        ====================================================== --}}

        <table class="info-grid">

            <tr>

                <td class="lbl">
                    Voucher No
                </td>

                <td class="val">
                    {{ $voucher->voucher_no ?? ('#' . $voucher->id) }}
                </td>

                <td class="lbl">
                    Due Date
                </td>

                <td class="val" style="font-weight:700">

                    @if($voucher->due_date)

                        {{ strtoupper(
                            \Carbon\Carbon::parse($voucher->due_date)->format('d-M-Y')
                        ) }}

                    @else

                        —

                    @endif

                </td>

            </tr>


            <tr>

                <td class="lbl">
                    Student Name
                </td>

                <td class="val" style="font-weight:700">

                    {{ strtoupper($voucher->student->student_name ?? '') }}

                </td>

                <td class="lbl">
                    GR / Adm No
                </td>

                <td class="val">

                    {{ strtoupper($voucher->student->admission_no ?? '—') }}

                </td>

            </tr>


            <tr>

                <td class="lbl">
                    Class
                </td>

                <td class="val">

                    {{ $voucher->student?->activeEnrollment?->class?->class_name ?? '—' }}

                </td>

                <td class="lbl">
                    Family Code
                </td>

                <td class="val">

                    {{ $voucher->student->family_code ?? '—' }}

                </td>

            </tr>


            <tr>

                <td class="lbl">
                    Period
                </td>

                <td class="val" colspan="3">

                    @if($voucher->period_from)

                        {{ strtoupper(
                            \Carbon\Carbon::parse($voucher->period_from)->format('d-M-Y')
                        ) }}

                    @endif

                    @if($voucher->period_to)

                        &nbsp;TO&nbsp;

                        {{ strtoupper(
                            \Carbon\Carbon::parse($voucher->period_to)->format('d-M-Y')
                        ) }}

                    @endif

                </td>

            </tr>


            <tr>

                <td class="lbl">
                    Voucher Type
                </td>

                <td class="val">

                    {{ ucfirst($voucher->voucher_type ?? 'Monthly') }}

                </td>

                <td class="lbl">
                    Status
                </td>

                <td class="val">

                    <span class="pill">
                        {{ ucfirst($status) }}
                    </span>

                </td>

            </tr>

        </table>


        {{-- =====================================================
             FEE ITEMS
        ====================================================== --}}

        <div class="section-title">
            Fee Items
        </div>


        <table class="items-table">

            <thead>

                <tr>

                    <th width="4%">
                        #
                    </th>

                    <th width="22%">
                        Fee Type
                    </th>

                    <th width="32%">
                        Description
                    </th>

                    <th width="14%">
                        Month
                    </th>

                    <th width="8%" class="text-center">
                        Qty
                    </th>

                    <th width="20%" class="text-right">
                        Amount (Rs.)
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
                            {{ $item->description ?: '—' }}
                        </td>

                        <td>

                            @if($item->month)

                                {{ strtoupper(
                                    \Carbon\Carbon::parse($item->month)->format('M Y')
                                ) }}

                            @else

                                —

                            @endif

                        </td>

                        <td class="text-center">
                            {{ $item->months_count ?? 1 }}
                        </td>

                        <td class="text-right mono">

                            {{ number_format(
                                (float) $item->amount,
                                0
                            ) }}

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="text-center"
                            style="padding:16px"
                        >
                            No fee items found.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>


        {{-- =====================================================
             TOTALS
        ====================================================== --}}

        <div class="totals-wrap">

            <table class="totals-table">

                <tr>

                    <td class="lbl">
                        Sub-Total
                    </td>

                    <td class="val">

                        {{ number_format(
                            (float) ($voucher->total_amount ?? 0),
                            0
                        ) }}

                    </td>

                </tr>


                @if(($voucher->discount ?? 0) > 0)

                    <tr>

                        <td class="lbl">
                            Discount
                        </td>

                        <td class="val">

                            ( {{ number_format(
                                (float) $voucher->discount,
                                0
                            ) }} )

                        </td>

                    </tr>

                @endif


                <tr class="row-payable">

                    <td class="lbl">
                        Payable Amount
                    </td>

                    <td class="val">

                        Rs.
                        {{ number_format(
                            (float) ($voucher->payable_amount ?? 0),
                            0
                        ) }}

                    </td>

                </tr>


                @if($paidAmount > 0)

                    <tr class="row-paid">

                        <td class="lbl">

                            Amount Received

                            @if($hasPayments)

                                <span style="font-weight:400;font-size:9px">

                                    ({{ $payments->count() }}
                                    payment{{ $payments->count() > 1 ? 's' : '' }})

                                </span>

                            @endif

                        </td>

                        <td class="val">

                            Rs.
                            {{ number_format(
                                $paidAmount,
                                0
                            ) }}

                        </td>

                    </tr>


                    <tr class="{{ $status === 'paid'
                        ? 'row-balance-zero'
                        : 'row-balance' }}">

                        <td class="lbl">
                            Balance Due
                        </td>

                        <td class="val">

                            Rs.
                            {{ number_format(
                                $status === 'paid'
                                    ? 0
                                    : $balanceAmount,
                                0
                            ) }}

                        </td>

                    </tr>

                @endif

            </table>

        </div>


        {{-- =====================================================
             AMOUNT IN WORDS
        ====================================================== --}}

        @if($voucher->amount_in_words)

            <div class="amount-words">

                <strong>
                    Amount in Words:
                </strong>

                {{ strtoupper($voucher->amount_in_words) }}

            </div>

        @endif


        {{-- =====================================================
             PAYMENT HISTORY
        ====================================================== --}}

        @if($hasPayments)

            <div
                class="section-title"
                style="margin-top:16px"
            >

                Payment History

                <span
                    style="
                        font-weight:400;
                        font-size:9px;
                        margin-left:8px;
                    "
                >

                    {{ $payments->count() }}
                    payment{{ $payments->count() > 1 ? 's' : '' }}
                    recorded

                </span>

            </div>


            <table class="payment-table">

                <thead>

                    <tr>

                        <th width="20%">
                            Payment Date
                        </th>

                        <th width="22%">
                            Receipt No
                        </th>

                        <th width="18%">
                            Method
                        </th>

                        <th width="22%">
                            Notes
                        </th>

                        <th
                            width="18%"
                            class="text-right"
                        >
                            Amount (Rs.)
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($payments as $pay)

                        <tr>

                            {{-- Payment Date --}}

                            <td>

                                @if($pay->payment_date)

                                    {{ strtoupper(
                                        \Carbon\Carbon::parse(
                                            $pay->payment_date
                                        )->format('d-M-Y')
                                    ) }}

                                @else

                                    —

                                @endif

                            </td>


                            {{-- Receipt Number --}}

                            <td class="mono">

                                {{ $pay->receipt_no ?? '—' }}

                            </td>


                            {{-- Payment Method --}}

                            <td>

                                {{ ucfirst(
                                    $pay->payment_method ?? 'Cash'
                                ) }}

                            </td>


                            {{-- Payment Notes --}}

                            <td style="font-size:10px">

                                {{ $pay->notes ?? '—' }}

                            </td>


                            {{-- ACTUAL PAYMENT AMOUNT FIELD --}}

                            <td
                                class="text-right mono"
                                style="font-weight:700"
                            >

                                {{ number_format(
                                    (float) ($pay->amount_paid ?? 0),
                                    0
                                ) }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>


                {{-- Payment total --}}

                <tfoot>

                    <tr>

                        <td
                            colspan="4"
                            style="text-align:right;font-weight:700"
                        >

                            Total Received:

                        </td>

                        <td class="text-right mono">

                            Rs.
                            {{ number_format(
                                (float) $payments->sum('amount_paid'),
                                0
                            ) }}

                        </td>

                    </tr>

                </tfoot>

            </table>

        @elseif($status === 'unpaid')

            {{-- No payments yet --}}

            <div class="flag-box">

                No payment received against this voucher.

                &nbsp;|&nbsp;

                Amount Due:

                <span class="mono">

                    Rs.
                    {{ number_format(
                        (float) ($voucher->payable_amount ?? 0),
                        0
                    ) }}

                </span>

            </div>

        @endif


        {{-- =====================================================
             NOTES
        ====================================================== --}}

        @if($voucher->notes)

            <div class="notes-box">

                <strong>
                    Notes:
                </strong>

                {{ $voucher->notes }}

            </div>

        @endif


        {{-- =====================================================
             PREVIOUS BALANCE
        ====================================================== --}}

        @if(isset($previousBalance) && (float) $previousBalance > 0)

            <div class="flag-box-left">

                <strong>
                    Other Outstanding Balance:
                </strong>

                Rs.
                {{ number_format(
                    (float) $previousBalance,
                    0
                ) }}

                due on previous vouchers.

            </div>

        @endif


        {{-- =====================================================
             FOOTER
        ====================================================== --}}

        <div class="footer">

            <div>
                Voucher ID: #{{ $voucher->id }}
                |
                Payment Method: Cash, Jazz Cash, EasyPaisa, Bank Transfer
            </div>

            <div>
                Generated:
                {{ now()
                    ->setTimezone('Asia/Karachi')
                    ->format('d-M-Y h:i A') }}
            </div>

        </div>


    </div>

</div>

</body>
</html>
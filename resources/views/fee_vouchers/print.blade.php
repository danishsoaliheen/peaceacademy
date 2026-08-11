<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ strtoupper($voucher->student->student_name ?? '') }} — {{ $voucher->voucher_no ?? $voucher->id }}</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <style>

        /* ── Reset & Base ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            background: var(--bg-body, #e5e5e5);
            color: #000;
            padding: 24px;
        }

        /* ── Screen-only controls ────────────────────── */
        .screen-bar {
            width: 760px;
            margin: 0 auto 16px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn-print {
            background: var(--accent-primary, #0f1f3d);
            color: #fff;
            border: none;
            padding: 9px 22px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .btn-print:hover { background: var(--accent-primary-hover, #1b3760); }
        .btn-back {
            background: transparent;
            color: var(--accent-primary, #0f1f3d);
            border: 1px solid var(--accent-primary, #0f1f3d);
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Voucher wrapper ─────────────────────────── */
        .voucher {
            width: 760px;
            margin: 0 auto;
            background: #fff;
            border: 1.5px solid #000;
        }

        /* ── School header ───────────────────────────── */
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #000;
            padding: 12px 16px;
        }
        .header img.logo {
            width: 56px;
            height: auto;
            margin-right: 14px;
        }
        .header .school { flex: 1; }
        .header .school-name {
            font-size: 19px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header .school-sub {
            font-size: 10.5px;
            margin-top: 2px;
        }

        /* ── Status banner (black bar, no color reliance) ── */
        .status-line {
            text-align: center;
            padding: 6px;
            font-size: 12px;
            font-weight: 700;
            border-bottom: 1px solid #000;
            letter-spacing: 1px;
        }

        /* ── Body padding ────────────────────────────── */
        .v-body { padding: 16px; }

        /* ── Info grid ───────────────────────────────── */
        table { width: 100%; border-collapse: collapse; font-size: 12px; }

        .info-grid td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .info-grid td.lbl {
            font-weight: 700;
            background: #eee;
            width: 18%;
            white-space: nowrap;
        }
        .info-grid td.val { width: 32%; }

        /* ── Clickable fields (student, family, receipt) ── */
        .v-link {
            color: #000;
            text-decoration: underline;
            font-weight: 700;
        }

        /* ── Section titles ──────────────────────────── */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: #000;
            color: #fff;
            padding: 5px 8px;
            margin-top: 14px;
        }

        /* ── Fee items table ─────────────────────────── */
        .items-table th {
            border: 1px solid #000;
            background: #eee;
            padding: 6px 8px;
            font-size: 11px;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'Courier New', monospace; }

        /* ── Totals block ────────────────────────────── */
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 12px;
        }
        .totals-table { width: 300px; }
        .totals-table td { border: 1px solid #000; padding: 5px 8px; }
        .totals-table td.lbl { font-weight: 700; background: #eee; }
        .totals-table td.val { text-align: right; }

        .totals-table .row-payable td {
            background: #000;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
        }
        .totals-table .row-paid td {
            font-weight: 700;
        }
        .totals-table .row-balance td {
            font-weight: 700;
            border-top: 2px solid #000;
        }
        .totals-table .row-balance-zero td {
            font-weight: 700;
            border-top: 2px solid #000;
        }

        /* ── Amount in words ─────────────────────────── */
        .amount-words {
            margin-top: 14px;
            border: 1px solid #000;
            padding: 8px 10px;
            font-size: 11.5px;
        }

        /* ── Payment history ─────────────────────────── */
        .payment-table th {
            border: 1px solid #000;
            background: #eee;
            padding: 5px 8px;
            font-size: 10.5px;
            text-align: left;
        }
        .payment-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 11px;
        }
        .payment-table tfoot td {
            font-weight: 700;
            background: #eee;
        }

        /* ── Status pill ─────────────────────────────── */
        .pill {
            border: 1px solid #000;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        /* ── Notes ───────────────────────────────────── */
        .notes-box {
            margin-top: 12px;
            border: 1px dashed #000;
            padding: 7px 10px;
            font-size: 11px;
        }

        /* ── Unpaid notice / previous balance ────────── */
        .flag-box {
            margin-top: 14px;
            padding: 8px 12px;
            border: 1px dashed #000;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }
        .flag-box-left {
            margin-top: 10px;
            padding: 7px 10px;
            border: 1px solid #000;
            font-size: 12px;
        }

        /* ── Footer (no signature line) ──────────────── */
        .footer {
            margin-top: 26px;
            padding-top: 12px;
            border-top: 1px dashed #000;
            font-size: 10.5px;
        }

        /* ── Print settings ──────────────────────────── */
        @media print {
            body { background: #fff; padding: 0; }
            .screen-bar { display: none; }
            .voucher {
                width: 100%;
                border: none;
            }
            @page { size: A4; margin: 10mm; }
        }

    </style>
</head>
<body>

@php
    $status       = strtolower($voucher->status ?? 'unpaid');
    $paidAmount   = $voucher->paid_amount ?? 0;
    $balanceAmount = $voucher->balance_amount ?? $voucher->payable_amount;
    $payments     = $voucher->payments ?? collect();
    $hasPayments  = $payments->count() > 0;

    $statusLabel = match($status) {
        'paid'    => '*** FULLY PAID ***',
        'partial' => '*** PARTIALLY PAID — BALANCE DUE ***',
        default   => '*** UNPAID — PAYMENT DUE ***',
    };

    $bannerClass = match($status) {
        'paid'    => 'paid',
        'partial' => 'partial',
        default   => 'unpaid',
    };
@endphp

{{-- ── Screen controls ── --}}
<div class="screen-bar">
    <button class="btn-print" onclick="window.print()">
        Print / Save PDF
    </button>
    <a href="{{ url()->previous() }}" class="btn-back">&larr; Back</a>
    <span style="margin-left:auto;font-size:12px;color:#000;">
        Voucher: <strong>{{ $voucher->voucher_no ?? ('#' . $voucher->id) }}</strong>
    </span>
</div>

{{-- ── Voucher card ── --}}
<div class="voucher">

    {{-- School header --}}
    <div class="header">
        <img class="logo" src="{{ asset('images/logo.png') }}" alt="Peace Academy Logo">
        <div class="school">
            <div class="school-name">PEACE ACADEMY</div>
            <div class="school-sub">Fee Management System</div>
        </div>
    </div>

    {{-- Status line --}}
    <div class="status-line">{{ $statusLabel }}</div>

    <div class="v-body">

        {{-- Info grid --}}
        <table class="info-grid">
            <tr>
                <td class="lbl">Voucher No</td>
                <td class="val">{{ $voucher->voucher_no ?? ('#' . $voucher->id) }}</td>
                <td class="lbl">Due Date</td>
                <td class="val" style="font-weight:700">
                    {{ strtoupper(\Carbon\Carbon::parse($voucher->due_date)->format('d-M-Y')) }}
                </td>
            </tr>
            <tr>
                <td class="lbl">Student Name</td>
                <td class="val" style="font-weight:700">{{ strtoupper($voucher->student->student_name ?? '') }}</td>
                <td class="lbl">GR / Adm No</td>
                <td class="val">
                    @if($voucher->student)
                        <a class="v-link" href="{{ route('students.show', $voucher->student->id) }}" target="_blank">
                            {{ strtoupper($voucher->student->admission_no ?? '') }}
                        </a>
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td class="lbl">Class</td>
                <td class="val">{{ $voucher->student?->activeEnrollment?->class?->class_name ?? '—' }}</td>
                <td class="lbl">Family Code</td>
                <td class="val">
                    @if(!empty($voucher->student->family_code))
                        <a class="v-link" href="{{ route('students.index', ['family_code' => $voucher->student->family_code]) }}" target="_blank">
                            {{ $voucher->student->family_code }}
                        </a>
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td class="lbl">Period</td>
                <td class="val" colspan="3">
                    {{ strtoupper(\Carbon\Carbon::parse($voucher->period_from)->format('d-M-Y')) }}
                    &nbsp;TO&nbsp;
                    {{ strtoupper(\Carbon\Carbon::parse($voucher->period_to)->format('d-M-Y')) }}
                </td>
            </tr>
            <tr>
                <td class="lbl">Voucher Type</td>
                <td class="val">{{ ucfirst($voucher->voucher_type ?? 'Monthly') }}</td>
                <td class="lbl">Status</td>
                <td class="val">
                    <span class="pill">{{ ucfirst($status) }}</span>
                </td>
            </tr>
        </table>

        {{-- Fee items --}}
        <div class="section-title">Fee Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th width="4%">#</th>
                    <th width="22%">Fee Type</th>
                    <th width="32%">Description</th>
                    <th width="14%">Month</th>
                    <th width="8%" class="text-center">Qty</th>
                    <th width="20%" class="text-right">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($voucher->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->feeType->name ?? '—' }}</td>
                    <td>{{ $item->description ?: '—' }}</td>
                    <td>
                        @if($item->month)
                            {{ strtoupper(\Carbon\Carbon::parse($item->month)->format('M Y')) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">{{ $item->months_count ?? 1 }}</td>
                    <td class="text-right mono">{{ number_format($item->amount, 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding:16px">No fee items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals-wrap">
            <table class="totals-table">
                <tr>
                    <td class="lbl">Sub-Total</td>
                    <td class="val">{{ number_format($voucher->total_amount ?? 0, 0) }}</td>
                </tr>
                @if(($voucher->discount ?? 0) > 0)
                <tr>
                    <td class="lbl">Discount</td>
                    <td class="val">( {{ number_format($voucher->discount, 0) }} )</td>
                </tr>
                @endif
                <tr class="row-payable">
                    <td class="lbl">Payable Amount</td>
                    <td class="val">Rs. {{ number_format($voucher->payable_amount ?? 0, 0) }}</td>
                </tr>
                @if($paidAmount > 0)
                <tr class="row-paid">
                    <td class="lbl">
                        Amount Received
                        @if($hasPayments)
                            <span style="font-weight:400;font-size:10px">({{ $payments->count() }} payment{{ $payments->count() > 1 ? 's' : '' }})</span>
                        @endif
                    </td>
                    <td class="val">Rs. {{ number_format($paidAmount, 0) }}</td>
                </tr>
                @if($status === 'paid')
                <tr class="row-balance-zero">
                    <td class="lbl">Balance Due</td>
                    <td class="val">Rs. 0</td>
                </tr>
                @else
                <tr class="row-balance">
                    <td class="lbl">Balance Due</td>
                    <td class="val">Rs. {{ number_format($balanceAmount, 0) }}</td>
                </tr>
                @endif
                @endif
            </table>
        </div>

        {{-- Amount in words --}}
        @if($voucher->amount_in_words)
        <div class="amount-words">
            <strong>Amount in Words:</strong>
            {{ strtoupper($voucher->amount_in_words) }}
        </div>
        @endif

        {{-- ══════════════════════════════════════════════
             PAYMENT HISTORY — shown only when payments exist
        ══════════════════════════════════════════════ --}}
        @if($hasPayments)
        <div class="payment-section">

            <div class="section-title" style="margin-top:16px">
                Payment History
                <span style="font-weight:400;font-size:10px;margin-left:8px">
                    {{ $payments->count() }} payment{{ $payments->count() > 1 ? 's' : '' }} recorded
                </span>
            </div>

            <table class="payment-table">
                <thead>
                    <tr>
                        <th width="20%">Payment Date</th>
                        <th width="22%">Receipt No</th>
                        <th width="18%">Method</th>
                        <th width="22%">Notes</th>
                        <th width="18%" class="text-right">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $i => $pay)
                    <tr>
                        <td>{{ strtoupper(\Carbon\Carbon::parse($pay->payment_date)->format('d-M-Y')) }}</td>
                        <td class="mono">
                            @php $vStatus = strtolower($voucher->status ?? ''); @endphp
                            @if(in_array($vStatus, ['paid', 'partial']))
                                <a href="{{ route('fee-payments.edit', $pay->id) }}"
                                   target="_blank"
                                   title="Click to correct payment method"
                                   class="v-link">
                                    {{ $pay->receipt_no ?? '—' }}
                                </a>
                            @else
                                {{ $pay->receipt_no ?? '—' }}
                            @endif
                        </td>
                        <td>{{ ucfirst($pay->payment_method ?? 'Cash') }}</td>
                        <td style="font-size:11px">{{ $pay->notes ?? '—' }}</td>
                        <td class="text-right mono" style="font-weight:700">{{ number_format($pay->amount_paid, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:700">Total Received:</td>
                        <td class="text-right mono">Rs. {{ number_format($payments->sum('amount_paid'), 0) }}</td>
                    </tr>
                </tfoot>
            </table>

        </div>
        @elseif($status === 'unpaid')
        {{-- No payments yet — show a clear unpaid notice --}}
        <div class="flag-box">
            No payment received against this voucher.
            &nbsp;|&nbsp; Amount Due: <span class="mono">Rs. {{ number_format($voucher->payable_amount ?? 0, 0) }}</span>
        </div>
        @endif

        {{-- Notes --}}
        @if($voucher->notes)
        <div class="notes-box" style="margin-top:14px">
            <strong>Notes:</strong> {{ $voucher->notes }}
        </div>
        @endif

        {{-- Previous balance notice (other vouchers) --}}
        @if(isset($previousBalance) && $previousBalance > 0)
        <div class="flag-box-left">
            <strong>Other Outstanding Balance:</strong>
            Rs. {{ number_format($previousBalance, 0) }} due on previous vouchers.
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div>Voucher ID: #{{ $voucher->id }} | Payment Method: Cash, Jazz Cash, EasyPaisa, Bank Transfer</div>
            <div>Printed: {{ now()->setTimezone('Asia/Karachi')->format('d-M-Y h:i A') }}</div>
        </div>

    </div>{{-- /v-body --}}
</div>{{-- /voucher --}}

<script>
    window.onload = function () { window.print(); };
</script>

</body>
</html>
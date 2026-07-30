@extends('layouts.dashboard')

@section('content')
<style>
    .ledger-wrap { max-width: 960px; margin: 0 auto; }

    /* Student info bar */
    .student-bar {
        background: #1e293b;
        color: white;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .student-bar .fields { display: flex; gap: 28px; flex-wrap: wrap; }
    .student-bar .field label {
        display: block;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #94a3b8;
        margin-bottom: 3px;
    }
    .student-bar .field span { font-size: 14px; font-weight: 600; }

    /* Summary pills */
    .summary-row {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .pill {
        flex: 1;
        min-width: 140px;
        background: white;
        border-radius: 8px;
        padding: 14px 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-top: 3px solid #e2e8f0;
    }
    .pill.charged  { border-top-color: #3b82f6; }
    .pill.paid     { border-top-color: #22c55e; }
    .pill.balance  { border-top-color: #ef4444; }
    .pill label { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
    .pill .val { font-size: 20px; font-weight: 700; color: #1e293b; }
    .pill.balance .val { color: #ef4444; }
    .pill.paid .val    { color: #16a34a; }

    /* Ledger table */
    .ledger-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .ledger-card-header {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .ledger-card-header h3 { margin: 0; font-size: 14px; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }

    table.ledger { width: 100%; border-collapse: collapse; }
    table.ledger th {
        padding: 9px 14px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
        font-weight: 600;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }
    table.ledger th.r { text-align: right; }
    table.ledger td {
        padding: 11px 14px;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    table.ledger td.r { text-align: right; }

    /* Row types */
    .tr-prev td { background: #fef9ec; font-weight: 600; }
    .tr-voucher td { background: #fff; }
    .tr-payment td { background: #f0fdf4; }
    .tr-balance td { background: #fef2f2; font-weight: 700; }

    .tag {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .tag-voucher  { background: #dbeafe; color: #1d4ed8; }
    .tag-payment  { background: #dcfce7; color: #15803d; }
    .tag-prev     { background: #fef3c7; color: #92400e; }

    .debit  { color: #dc2626; font-weight: 600; }
    .credit { color: #16a34a; font-weight: 600; }
    .bal    { color: #1e293b; font-weight: 700; }
    .bal.zero { color: #16a34a; }

    /* Clickable voucher / receipt links */
    .vno-link {
        font-family: monospace;
        font-size: 11px;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 3px;
        color: #1d4ed8;
        text-decoration: none;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    .vno-link:hover {
        background: #dbeafe;
        color: #1e40af;
        text-decoration: underline;
    }
    .vno-plain {
        font-family: monospace;
        font-size: 11px;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 3px;
        color: #475569;
    }

    .rcpt-link {
        font-family: monospace;
        font-size: 11px;
        color: #7c3aed;
        text-decoration: none;
        cursor: pointer;
    }
    .rcpt-link:hover {
        color: #6d28d9;
        text-decoration: underline;
    }

    .btn-new {
        padding: 8px 16px;
        background: #0d6efd;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }
    .btn-new:hover { opacity: .88; color: white; }
    .btn-back {
        padding: 8px 14px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
    }
    .btn-action {
        font-size: 12px;
        color: #0d6efd;
        text-decoration: none;
    }
    .btn-action:hover { text-decoration: underline; }
    .btn-action.pay { color: #16a34a; }

    .empty { text-align: center; padding: 40px; color: #94a3b8; font-size: 14px; }
</style>

@php
    /*
    |------------------------------------------------------------------
    | Build a flat list of ledger rows sorted by date:
    |  - Previous balance  (opening)  — only when date filter applied
    |  - Fee voucher       (debit)
    |  - Payment           (credit)
    |
    | Running balance carried forward across all rows.
    | When NO date filter, previousBalance = 0 so all vouchers
    | are included and there is no double-counting.
    |------------------------------------------------------------------
    */

    $rows           = [];
    $runningBalance = 0;

    // 1. Opening / previous balance row (only when date filter is applied)
    if ($previousBalance > 0 && $cutoff) {
        $runningBalance += $previousBalance;
        $rows[] = [
            'date'    => $cutoff,
            'type'    => 'prev',
            'label'   => 'Opening Balance (brought forward)',
            'voucher' => null,
            'payment' => null,
            'debit'   => $previousBalance,
            'credit'  => null,
            'balance' => $runningBalance,
        ];
    }

    // 2. Vouchers + their payments, interleaved in date order
    foreach ($vouchers->sortBy('due_date') as $v) {

        $runningBalance += $v->payable_amount;

        $rows[] = [
            'date'    => $v->due_date,
            'type'    => 'voucher',
            'label'   => ($v->period_from
                            ? \Carbon\Carbon::parse($v->period_from)->format('M Y') . ' Fee'
                            : 'Fee Voucher'),
            'voucher' => $v,
            'payment' => null,
            'debit'   => $v->payable_amount,
            'credit'  => null,
            'balance' => $runningBalance,
        ];

        foreach ($v->payments->sortBy('payment_date') as $p) {
            $runningBalance -= $p->amount_paid;
            $rows[] = [
                'date'    => $p->payment_date,
                'type'    => 'payment',
                'label'   => 'Payment — ' . ($p->payment_method ?? 'Cash'),
                'voucher' => $v,
                'payment' => $p,
                'debit'   => null,
                'credit'  => $p->amount_paid,
                'balance' => $runningBalance,
            ];
        }
    }

    // Sort all rows by date (prev/opening always first for same date)
    usort($rows, function($a, $b) {
        $d = strtotime($a['date']) - strtotime($b['date']);
        if ($d !== 0) return $d;
        $order = ['prev' => 0, 'voucher' => 1, 'payment' => 2];
        return ($order[$a['type']] ?? 1) - ($order[$b['type']] ?? 1);
    });
@endphp

<div class="ledger-wrap">

    {{-- Student Bar --}}
    <div class="student-bar">
        <div class="fields">
            <div class="field">
                <label>Student</label>
                <span>{{ strtoupper($student->student_name) }}</span>
            </div>
            <div class="field">
                <label>Adm. No</label>
                <span>{{ $student->admission_no }}</span>
            </div>
            <div class="field">
                <label>Father</label>
                <span>{{ strtoupper($student->father_name ?? '—') }}</span>
            </div>
            <div class="field">
                <label>Class</label>
                <span>{{ $enrollment?->class?->class_name ?? '—' }}</span>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('student-ledger.index') }}" class="btn-back">← Back</a>
            <a href="{{ route('fee-vouchers.create', ['student_id' => $student->id]) }}" class="btn-new">+ New Voucher</a>
        </div>
    </div>

    {{-- Summary Pills --}}
    <div class="summary-row">
        <div class="pill charged">
            <label>Total Charged</label>
            <div class="val">Rs. {{ number_format($totalCharged + $previousBalance, 0) }}</div>
        </div>
        <div class="pill paid">
            <label>Total Paid</label>
            <div class="val">Rs. {{ number_format($totalPaid, 0) }}</div>
        </div>
        <div class="pill balance">
            <label>Outstanding</label>
            <div class="val">Rs. {{ number_format($grandOutstanding, 0) }}</div>
        </div>
    </div>

    {{-- Ledger Table --}}
    <div class="ledger-card">

        <div class="ledger-card-header">
            <h3>Account Statement</h3>
            <span style="font-size:12px; color:#94a3b8;">
                {{ count($rows) }} transaction(s)
            </span>
        </div>

        @if(count($rows) > 0)

        <table class="ledger">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Voucher / Receipt</th>
                    <th class="r">Debit</th>
                    <th class="r">Credit</th>
                    <th class="r">Balance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            @foreach($rows as $row)
            <tr class="tr-{{ $row['type'] }}">

                <td style="white-space:nowrap; color:#64748b; font-size:12px;">
                    {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                </td>

                <td>
                    @if($row['type'] === 'prev')
                        <span class="tag tag-prev">B/F</span>
                    @elseif($row['type'] === 'voucher')
                        <span class="tag tag-voucher">Fee</span>
                    @else
                        <span class="tag tag-payment">Paid</span>
                    @endif
                    &nbsp;{{ $row['label'] }}
                </td>

                {{-- Voucher / Receipt column --}}
                <td>
                    @if($row['type'] === 'voucher' && $row['voucher'])
                        <a href="{{ route('fee-vouchers.print', $row['voucher']->id) }}"
                           target="_blank"
                           class="vno-link"
                           title="Click to view voucher">
                            {{ $row['voucher']->voucher_no }}
                            <i class="fas fa-external-link-alt" style="font-size:9px;margin-left:2px;"></i>
                        </a>
                    @elseif($row['type'] === 'payment' && $row['payment']?->receipt_no)
                        <a href="{{ route('fee-payments.receipt', $row['payment']->id) }}"
                           target="_blank"
                           class="rcpt-link"
                           title="Click to view receipt">
                            <i class="fas fa-receipt" style="font-size:10px;margin-right:3px;"></i>
                            {{ $row['payment']->receipt_no }}
                        </a>
                    @else
                        <span style="color:#cbd5e1;">—</span>
                    @endif
                </td>

                <td class="r debit">
                    @if($row['debit'])
                        {{ number_format($row['debit'], 0) }}
                    @endif
                </td>

                <td class="r credit">
                    @if($row['credit'])
                        {{ number_format($row['credit'], 0) }}
                    @endif
                </td>

                <td class="r">
                    <span class="bal {{ $row['balance'] == 0 ? 'zero' : '' }}">
                        Rs. {{ number_format($row['balance'], 0) }}
                    </span>
                </td>

                <td>
                    @if($row['type'] === 'voucher' && $row['voucher'])
                        <a href="{{ route('fee-vouchers.print', $row['voucher']->id) }}"
                           target="_blank"
                           class="btn-action">
                            <i class="fas fa-print"></i> View
                        </a>
                        @if(strtolower($row['voucher']->status) !== 'paid')
                            &nbsp;·&nbsp;
                            <a href="{{ route('fee-payments.create', $row['voucher']->id) }}"
                               class="btn-action pay">
                                <i class="fas fa-plus-circle"></i> Pay
                            </a>
                        @endif
                    @elseif($row['type'] === 'payment' && $row['payment'])
                        <a href="{{ route('fee-payments.receipt', $row['payment']->id) }}"
                           target="_blank"
                           class="btn-action">
                            <i class="fas fa-receipt"></i> Receipt
                        </a>
                    @endif
                </td>

            </tr>
            @endforeach

            </tbody>
            <tfoot>
                <tr class="tr-balance">
                    <td colspan="3" style="text-align:right; color:#64748b;">Closing Balance</td>
                    <td class="r debit">{{ number_format($totalCharged + $previousBalance, 0) }}</td>
                    <td class="r credit">{{ number_format($totalPaid, 0) }}</td>
                    <td class="r">
                        <span class="bal {{ $grandOutstanding == 0 ? 'zero' : '' }}">
                            Rs. {{ number_format($grandOutstanding, 0) }}
                        </span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        @else
        <div class="empty">No transactions found for this student.</div>
        @endif

    </div>

</div>

@endsection
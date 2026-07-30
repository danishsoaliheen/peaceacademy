@extends('layouts.dashboard')

@section('content')

@php
    $monthLabel = date('F Y', strtotime($monthStart));
@endphp

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-book me-2" style="opacity:.8;"></i>Monthly Accounting Ledger</h2>
            <p>Income vs Expenses · Outstanding Recovery · {{ $monthLabel }}</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="hero-stat">
                <div class="num" style="color:#4ade80;">{{ number_format($totalIncome) }}</div>
                <div class="lbl">Income (PKR)</div>
            </div>
            <div class="hero-stat">
                <div class="num" style="color:#f87171;">{{ number_format($totalExpenses) }}</div>
                <div class="lbl">Expenses (PKR)</div>
            </div>
            <div class="hero-stat">
                <div class="num" style="{{ $netBalance >= 0 ? 'color:#4ade80' : 'color:#f87171' }}">
                    {{ $netBalance >= 0 ? '+' : '' }}{{ number_format($netBalance) }}
                </div>
                <div class="lbl">Net Balance</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Month Picker ──────────────────────────────────────────────────────── --}}
<div class="section-card card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
            <label class="form-label mb-0" style="font-weight:700;white-space:nowrap;">
                <i class="fas fa-calendar-alt me-1 text-primary"></i> Select Month:
            </label>
            <input type="month" name="month" value="{{ $month }}"
                   class="form-control" style="max-width:200px;"
                   onchange="this.form.submit()">
            <a href="{{ route('monthly-ledger.index') }}" class="btn btn-sm btn-outline-secondary">
                Current Month
            </a>
        </form>
    </div>
</div>

{{-- ── 4 KPI Cards ──────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Total Billed --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 rounded-3 h-100" style="background:#fff;box-shadow:0 1px 6px rgba(0,0,0,.08);">
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div style="width:48px;height:48px;background:#dbeafe;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#1d4ed8;flex-shrink:0;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Total Billed</div>
                    <div style="font-size:1.4rem;font-weight:800;color:#1e293b;">{{ number_format($totalBilled) }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;">PKR · {{ $monthLabel }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fee Collected --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 rounded-3 h-100" style="background:#fff;box-shadow:0 1px 6px rgba(0,0,0,.08);">
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div style="width:48px;height:48px;background:#dcfce7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#16a34a;flex-shrink:0;">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div>
                    <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Fee Collected</div>
                    <div style="font-size:1.4rem;font-weight:800;color:#16a34a;">{{ number_format($totalIncome) }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;">{{ $recoveryPct }}% recovery rate</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 rounded-3 h-100" style="background:#fff;box-shadow:0 1px 6px rgba(0,0,0,.08);">
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div style="width:48px;height:48px;background:#fee2e2;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#dc2626;flex-shrink:0;">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Total Expenses</div>
                    <div style="font-size:1.4rem;font-weight:800;color:#dc2626;">{{ number_format($totalExpenses) }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;">{{ $expenses->count() }} entries</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Net Balance --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 rounded-3 h-100" style="background:{{ $netBalance >= 0 ? '#f0fdf4' : '#fef2f2' }};box-shadow:0 1px 6px rgba(0,0,0,.08);">
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div style="width:48px;height:48px;background:{{ $netBalance >= 0 ? '#bbf7d0' : '#fecaca' }};border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:{{ $netBalance >= 0 ? '#16a34a' : '#dc2626' }};flex-shrink:0;">
                    <i class="fas {{ $netBalance >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                </div>
                <div>
                    <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Net Balance</div>
                    <div style="font-size:1.4rem;font-weight:800;color:{{ $netBalance >= 0 ? '#16a34a' : '#dc2626' }};">
                        {{ $netBalance >= 0 ? '+' : '' }}{{ number_format($netBalance) }}
                    </div>
                    <div style="font-size:.72rem;color:#94a3b8;">Income − Expenses</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Main Two-Column Ledger ───────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- LEFT: Income / Payments Received --}}
    <div class="col-lg-6">
        <div class="section-card card h-100">
            <div class="card-header" style="justify-content:space-between;">
                <div class="d-flex align-items-center gap-2">
                    <span class="s-icon" style="background:#16a34a;"><i class="fas fa-arrow-down"></i></span>
                    <h6>Fee Payments Received</h6>
                </div>
                <span class="pa-badge pa-badge-green">PKR {{ number_format($totalIncome) }}</span>
            </div>

            {{-- Method breakdown --}}
            @if($incomeByMethod->count())
            <div class="px-3 pt-3 pb-0 d-flex flex-wrap gap-2">
                @php
                    $methodColors = ['Cash'=>['#dcfce7','#16a34a'],'Bank Transfer'=>['#dbeafe','#1d4ed8'],'EasyPaisa'=>['#fce7f3','#be185d'],'JazzCash'=>['#fef3c7','#d97706']];
                @endphp
                @foreach($incomeByMethod as $method => $amt)
                    @php [$bg,$tc] = $methodColors[$method] ?? ['#f1f5f9','#475569']; @endphp
                    <div style="background:{{ $bg }};border-radius:8px;padding:6px 14px;font-size:.78rem;">
                        <span style="color:{{ $tc }};font-weight:700;">{{ $method }}</span>
                        <span style="color:#64748b;margin-left:4px;">PKR {{ number_format($amt) }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
                <table class="table pa-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Receipt #</th>
                            <th>Student</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td style="font-size:.8rem;white-space:nowrap;">{{ \Carbon\Carbon::parse($p->payment_date)->format('d M') }}</td>
                            <td>
                                <span style="font-family:monospace;font-size:.75rem;background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#475569;">
                                    {{ $p->receipt_no }}
                                </span>
                            </td>
                            <td style="font-size:.83rem;font-weight:600;">{{ strtoupper($p->student->student_name ?? '—') }}</td>
                            <td>
                                @php [$bg,$tc] = $methodColors[$p->payment_method] ?? ['#f1f5f9','#475569']; @endphp
                                <span style="background:{{ $bg }};color:{{ $tc }};padding:2px 8px;border-radius:5px;font-size:.73rem;font-weight:700;">
                                    {{ $p->payment_method }}
                                </span>
                            </td>
                            <td class="text-end" style="font-weight:700;color:#16a34a;white-space:nowrap;">
                                {{ number_format($p->amount_paid) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted" style="font-size:.84rem;">
                                <i class="fas fa-inbox d-block mb-1 opacity-25 fa-lg"></i>
                                No payments received this month
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($payments->count())
                    <tfoot>
                        <tr style="background:#f0fdf4;">
                            <td colspan="4" style="font-weight:700;font-size:.85rem;padding:10px 16px;">Total Received</td>
                            <td class="text-end" style="font-weight:800;color:#16a34a;font-size:.95rem;padding:10px 16px;">
                                PKR {{ number_format($totalIncome) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- RIGHT: Expenses --}}
    <div class="col-lg-6">
        <div class="section-card card h-100">
            <div class="card-header" style="justify-content:space-between;">
                <div class="d-flex align-items-center gap-2">
                    <span class="s-icon" style="background:#dc2626;"><i class="fas fa-arrow-up"></i></span>
                    <h6>Expenses Paid Out</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="pa-badge pa-badge-red">PKR {{ number_format($totalExpenses) }}</span>
                    <a href="{{ route('expenses.create') }}" class="btn-icon btn-icon-blue" style="font-size:.72rem;padding:4px 10px;">
                        <i class="fas fa-plus"></i> Add
                    </a>
                </div>
            </div>

            {{-- Category breakdown pills --}}
            @if($expenseByCategory->count())
            <div class="px-3 pt-3 pb-0 d-flex flex-wrap gap-2">
                @foreach($expenseByCategory as $cat => $amt)
                    @php $c = \App\Models\Expense::categoryColor($cat); @endphp
                    <div style="background:{{ $c['bg'] }};border-radius:8px;padding:6px 14px;font-size:.78rem;">
                        <span style="color:{{ $c['text'] }};font-weight:700;">{{ $cat }}</span>
                        <span style="color:#64748b;margin-left:4px;">PKR {{ number_format($amt) }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
                <table class="table pa-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description / Paid To</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $exp)
                        @php $c = \App\Models\Expense::categoryColor($exp->category); @endphp
                        <tr>
                            <td style="font-size:.8rem;white-space:nowrap;">{{ \Carbon\Carbon::parse($exp->expense_date)->format('d M') }}</td>
                            <td>
                                <span style="background:{{ $c['bg'] }};color:{{ $c['text'] }};padding:2px 8px;border-radius:5px;font-size:.73rem;font-weight:700;">
                                    {{ $exp->sub_category ?: $exp->category }}
                                </span>
                            </td>
                            <td style="font-size:.82rem;color:#475569;">
                                {{ $exp->description ?: ($exp->paid_to ?: '—') }}
                            </td>
                            <td class="text-end" style="font-weight:700;color:#dc2626;white-space:nowrap;">
                                {{ number_format($exp->amount) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted" style="font-size:.84rem;">
                                <i class="fas fa-inbox d-block mb-1 opacity-25 fa-lg"></i>
                                No expenses recorded this month
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($expenses->count())
                    <tfoot>
                        <tr style="background:#fef2f2;">
                            <td colspan="3" style="font-weight:700;font-size:.85rem;padding:10px 16px;">Total Expenses</td>
                            <td class="text-end" style="font-weight:800;color:#dc2626;font-size:.95rem;padding:10px 16px;">
                                PKR {{ number_format($totalExpenses) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── Net Summary Box ──────────────────────────────────────────────────── --}}
<div class="section-card card mb-4" style="background:{{ $netBalance >= 0 ? '#f0fdf4' : '#fef2f2' }};border:2px solid {{ $netBalance >= 0 ? '#86efac' : '#fca5a5' }} !important;">
    <div class="card-body">
        <div class="row text-center g-0">
            <div class="col-4 border-end">
                <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Total Income</div>
                <div style="font-size:1.5rem;font-weight:800;color:#16a34a;">PKR {{ number_format($totalIncome) }}</div>
            </div>
            <div class="col-4 border-end">
                <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Total Expenses</div>
                <div style="font-size:1.5rem;font-weight:800;color:#dc2626;">PKR {{ number_format($totalExpenses) }}</div>
            </div>
            <div class="col-4">
                <div style="font-size:.72rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Net Balance</div>
                <div style="font-size:1.5rem;font-weight:800;color:{{ $netBalance >= 0 ? '#16a34a' : '#dc2626' }};">
                    {{ $netBalance >= 0 ? '+' : '' }}PKR {{ number_format(abs($netBalance)) }}
                </div>
                <div style="font-size:.72rem;color:#94a3b8;">
                    {{ $netBalance >= 0 ? 'Surplus' : 'Deficit' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Outstanding Defaulters ───────────────────────────────────────────── --}}
@if($outstandingVouchers->count())
<div class="section-card card mb-4">
    <div class="card-header" style="justify-content:space-between;">
        <div class="d-flex align-items-center gap-2">
            <span class="s-icon" style="background:#f97316;"><i class="fas fa-exclamation-triangle"></i></span>
            <h6>Outstanding / Defaulters — {{ $monthLabel }}</h6>
        </div>
        <span class="pa-badge pa-badge-orange">
            {{ $outstandingVouchers->count() }} students · PKR {{ number_format($totalOutstanding) }} pending
        </span>
    </div>
    <div class="table-responsive">
        <table class="table pa-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Voucher No</th>
                    <th class="text-end">Billed</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($outstandingVouchers as $i => $v)
                <tr>
                    <td style="color:#94a3b8;font-size:.78rem;">{{ $i+1 }}</td>
                    <td>
                        <div style="font-weight:700;font-size:.85rem;">{{ strtoupper($v->student->student_name ?? '—') }}</div>
                        <div style="font-size:.74rem;color:#94a3b8;">{{ $v->student->admission_no ?? '' }}</div>
                    </td>
                    <td>
                        @php $class = $v->student->activeEnrollment?->class?->class_name; @endphp
                        @if($class)
                            <span class="pa-badge pa-badge-blue">{{ $class }}</span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:.78rem;background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#475569;">
                            {{ $v->voucher_no }}
                        </span>
                    </td>
                    <td class="text-end" style="font-size:.84rem;">{{ number_format($v->payable_amount) }}</td>
                    <td class="text-end" style="font-size:.84rem;color:#16a34a;font-weight:600;">{{ number_format($v->paid_amount) }}</td>
                    <td class="text-end" style="font-weight:800;color:#dc2626;">{{ number_format($v->balance_amount) }}</td>
                    <td>
                        @php $st = strtolower($v->status); @endphp
                        @if($st === 'unpaid')
                            <span class="pa-badge pa-badge-red">Unpaid</span>
                        @else
                            <span class="pa-badge pa-badge-orange">Partial</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('fee-payments.create', $v->id) }}" class="btn-icon btn-icon-green">
                            <i class="fas fa-money-bill-wave"></i> Receive
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#fff7ed;">
                    <td colspan="6" style="font-weight:700;padding:10px 16px;font-size:.85rem;">Total Outstanding</td>
                    <td class="text-end" style="font-weight:800;color:#dc2626;font-size:.95rem;padding:10px 16px;">PKR {{ number_format($totalOutstanding) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- ── 6-Month Trend Bar Chart ──────────────────────────────────────────── --}}
<div class="section-card card">
    <div class="card-header">
        <span class="s-icon" style="background:#6366f1;"><i class="fas fa-chart-bar"></i></span>
        <h6>6-Month Trend</h6>
    </div>
    <div class="card-body">
        @php
            $maxVal = collect($trend)->max(fn($t) => max($t['income'], $t['expenses']));
            $maxVal = $maxVal ?: 1;
        @endphp
        <div class="d-flex align-items-end gap-3 justify-content-around" style="height:160px;">
            @foreach($trend as $t)
            @php
                $incH  = max(4, round(($t['income']   / $maxVal) * 130));
                $expH  = max(4, round(($t['expenses'] / $maxVal) * 130));
            @endphp
            <div class="text-center" style="flex:1;">
                <div class="d-flex align-items-end justify-content-center gap-1" style="height:130px;">
                    <div title="Income: PKR {{ number_format($t['income']) }}"
                         style="width:18px;height:{{ $incH }}px;background:#22c55e;border-radius:4px 4px 0 0;"></div>
                    <div title="Expenses: PKR {{ number_format($t['expenses']) }}"
                         style="width:18px;height:{{ $expH }}px;background:#ef4444;border-radius:4px 4px 0 0;"></div>
                </div>
                <div style="font-size:.7rem;color:#64748b;margin-top:4px;font-weight:600;">{{ $t['label'] }}</div>
            </div>
            @endforeach
        </div>
        <div class="d-flex gap-4 justify-content-center mt-2">
            <span style="font-size:.75rem;color:#64748b;"><span style="display:inline-block;width:12px;height:12px;background:#22c55e;border-radius:2px;margin-right:4px;"></span>Income</span>
            <span style="font-size:.75rem;color:#64748b;"><span style="display:inline-block;width:12px;height:12px;background:#ef4444;border-radius:2px;margin-right:4px;"></span>Expenses</span>
        </div>
    </div>
</div>

@endsection
@extends('layouts.dashboard')

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-receipt me-2" style="opacity:.8;"></i>Expenses</h2>
            <p>Record and manage all school expenses — salaries, utilities, maintenance and more</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="hero-stat">
                <div class="num" style="color:#f87171;">{{ number_format($monthTotal) }}</div>
                <div class="lbl">{{ date('M Y', strtotime($month.'-01')) }} Total</div>
            </div>
            <a href="{{ route('expenses.create') }}" class="btn-hero-ghost">
                <i class="fas fa-plus"></i> Add Expense
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="section-card card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="d-flex align-items-end gap-3 flex-wrap">
            <div>
                <label class="form-label">Month</label>
                <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="category" class="form-select form-select-sm" style="min-width:170px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach(array_keys(\App\Models\Expense::categories()) as $cat)
                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

@if($summary->count())
<div class="row g-3 mb-3">
    @foreach($summary as $cat => $total)
        @php $c = \App\Models\Expense::categoryColor($cat); @endphp
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 rounded-3" style="background:{{ $c['bg'] }};border-left:4px solid {{ $c['border'] }} !important;">
                <div class="card-body py-3 px-4 d-flex align-items-center gap-3">
                    <div style="width:36px;height:36px;background:{{ $c['border'] }};border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                        <i class="fas {{ $c['icon'] }}"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.4px;">{{ $cat }}</div>
                        <div style="font-size:1.05rem;font-weight:800;color:{{ $c['text'] }};">PKR {{ number_format($total) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 rounded-3" style="background:#1e293b;">
            <div class="card-body py-3 px-4 d-flex align-items-center gap-3">
                <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;">
                    <i class="fas fa-sigma"></i>
                </div>
                <div>
                    <div style="font-size:.7rem;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.4px;">Grand Total</div>
                    <div style="font-size:1.05rem;font-weight:800;color:#f87171;">PKR {{ number_format($monthTotal) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="section-card card">
    <div class="card-header" style="justify-content:space-between;">
        <div class="d-flex align-items-center gap-2">
            <span class="s-icon" style="background:#dc2626;"><i class="fas fa-list"></i></span>
            <h6>Expense Records <span class="pa-badge pa-badge-gray ms-1">{{ date('M Y', strtotime($month.'-01')) }}</span></h6>
        </div>
        <span style="font-size:.76rem;color:#94a3b8;">{{ $expenses->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table pa-table mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Exp No</th><th>Date</th><th>Category</th>
                    <th>Description / Paid To</th><th>Method</th>
                    <th class="text-end">Amount (PKR)</th><th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $i => $exp)
                @php $c = \App\Models\Expense::categoryColor($exp->category); @endphp
                <tr>
                    <td style="color:#94a3b8;font-size:.76rem;">{{ $expenses->firstItem() + $i }}</td>
                    <td><span style="font-family:monospace;font-size:.76rem;background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#475569;">{{ $exp->expense_no }}</span></td>
                    <td style="font-size:.83rem;white-space:nowrap;">{{ $exp->expense_date->format('d M Y') }}</td>
                    <td><span style="background:{{ $c['bg'] }};color:{{ $c['text'] }};padding:2px 9px;border-radius:5px;font-size:.73rem;font-weight:700;">{{ $exp->sub_category ?: $exp->category }}</span></td>
                    <td style="font-size:.83rem;color:#475569;">
                        <div>{{ $exp->description ?: '—' }}</div>
                        @if($exp->paid_to)<div style="font-size:.75rem;color:#94a3b8;">→ {{ $exp->paid_to }}</div>@endif
                    </td>
                    <td style="font-size:.8rem;">{{ $exp->payment_method }}</td>
                    <td class="text-end" style="font-weight:800;color:#dc2626;white-space:nowrap;">{{ number_format($exp->amount) }}</td>
                    <td class="text-center">
                        <a href="{{ route('expenses.edit', $exp) }}" class="btn-icon btn-icon-yellow"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('expenses.destroy', $exp) }}" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon btn-icon-red"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>
                        No expenses found. <a href="{{ route('expenses.create') }}" class="d-block mt-2 text-primary">+ Add First Expense</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
    <div class="card-body border-top pt-3">{{ $expenses->links('vendor.pagination.bootstrap-5') }}</div>
    @endif
</div>

@endsection

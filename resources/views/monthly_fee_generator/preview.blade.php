{{-- Save as: resources/views/monthly_fee_generator/preview.blade.php --}}
@extends('layouts.dashboard')

@section('content')

@php
    $toGenerate      = $rows->where('already_exists', false)->where('fee_amount', '>', 0);
    $willSkipExists  = $rows->where('already_exists', true);
    $willSkipNoFee   = $rows->where('already_exists', false)->where('fee_amount', '<=', 0);
    $totalPrevBal    = $toGenerate->sum('previous_balance');
    $studentsWithPB  = $toGenerate->where('previous_balance', '>', 0)->count();
    $totalPayable    = $toGenerate->sum('payable');
@endphp

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-eye me-2" style="opacity:.8;"></i>Preview — {{ $class->class_name ?? '' }} / {{ $session->session_name ?? '' }}</h2>
            <p>Nothing has been saved yet. Review the list below, then confirm to generate.</p>
        </div>
        <a href="{{ route('monthly-fee-generator.create') }}" class="btn-hero-ghost">
            <i class="fas fa-arrow-left"></i> Back &amp; Edit Settings
        </a>
    </div>
</div>

{{-- ── Fee structure breakdown — shows exactly what's being summed ───────── --}}
<div class="alert alert-light border mb-3" style="font-size:.85rem;">
    <strong><i class="fas fa-receipt me-1"></i>This month's fee is made up of:</strong>
    @forelse($feeStructure as $fee)
        <span class="ms-2">{{ $fee->feeType->name ?? 'Fee' }} (Rs {{ number_format($fee->amount) }})</span>@if(!$loop->last) + @endif
    @empty
        <span class="ms-2 text-muted">No "monthly" category fee structure found for this class — falling back to each student's individual enrollment fee.</span>
    @endforelse
    @if($feeStructure->count())
        <strong class="ms-2">= Rs {{ number_format($feeStructure->sum('amount')) }} per student</strong>
    @endif
    <div class="text-muted mt-1">
        Only fee types tagged category = "monthly" are included here. One-time fees (e.g. Admission Fee) are excluded automatically.
    </div>
</div>

{{-- ── Summary stats ────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="section-card card text-center py-3">
            <div class="fw-bold" style="font-size:1.5rem; color:#16a34a;">{{ $toGenerate->count() }}</div>
            <div class="text-muted small">Will Generate</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="section-card card text-center py-3">
            <div class="fw-bold" style="font-size:1.5rem; color:#64748b;">{{ $willSkipExists->count() }}</div>
            <div class="text-muted small">Already Exists</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="section-card card text-center py-3">
            <div class="fw-bold" style="font-size:1.5rem; color:#dc2626;">{{ $willSkipNoFee->count() }}</div>
            <div class="text-muted small">No Fee Configured</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="section-card card text-center py-3">
            <div class="fw-bold" style="font-size:1.5rem; color:#1e293b;">Rs {{ number_format($totalPayable) }}</div>
            <div class="text-muted small">Total Payable</div>
        </div>
    </div>
</div>

@if($include_previous_balance && $studentsWithPB > 0)
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-1"></i>
        Previous balance will be added for <strong>{{ $studentsWithPB }}</strong> student(s),
        totalling <strong>Rs {{ number_format($totalPrevBal) }}</strong>.
        Their source voucher(s) will be marked <strong>Carried Forward (C.F)</strong> and zeroed out
        once you confirm below.
    </div>
@endif

{{-- ── Rows table ───────────────────────────────────────────────────────── --}}
<div class="section-card card">
    <div class="card-header">
        <span class="s-icon" style="background:#1e293b;"><i class="fas fa-list"></i></span>
        <h6>Student Breakdown</h6>
    </div>
    <div class="table-responsive">
        <table class="table pa-table mb-0">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Fee Amount</th>
                    <th>Discount</th>
                    @if($include_previous_balance)
                        <th>Previous Balance</th>
                    @endif
                    <th>Payable</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr style="{{ $row['already_exists'] || $row['fee_amount'] <= 0 ? 'opacity:.55;' : '' }}">
                        <td>
                            <div class="fw-bold" style="color:#1e293b;">
                                {{ $row['enrollment']->student->student_name ?? '—' }}
                            </div>
                            <span class="text-muted" style="font-size:.78rem;">
                                {{ $row['enrollment']->student->admission_no ?? '' }}
                            </span>
                        </td>
                        <td>Rs {{ number_format($row['fee_amount']) }}</td>
                        <td>{{ $row['discount'] > 0 ? '- Rs '.number_format($row['discount']) : '—' }}</td>
                        @if($include_previous_balance)
                            <td class="{{ $row['previous_balance'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                @if($row['previous_balance'] > 0)
                                    + Rs {{ number_format($row['previous_balance']) }}
                                    <div class="text-muted" style="font-size:.72rem; font-weight:normal;">
                                        From: {{ $row['previous_balance_vouchers']->pluck('voucher_no')->implode(', ') }}
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        @endif
                        <td class="fw-bold">Rs {{ number_format($row['payable']) }}</td>
                        <td>
                            @if($row['already_exists'])
                                <span class="pa-badge pa-badge-gray">Already Generated</span>
                            @elseif($row['fee_amount'] <= 0)
                                <span class="pa-badge pa-badge-red">No Fee Set</span>
                            @else
                                <span class="pa-badge pa-badge-green">Will Generate</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No active students found in this class/session.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Confirm & generate ──────────────────────────────────────────────── --}}
@if($toGenerate->count() > 0)
    <form method="POST" action="{{ route('monthly-fee-generator.store') }}" class="mt-3"
          onsubmit="return confirm('Generate {{ $toGenerate->count() }} voucher(s) now? This cannot be undone in bulk.')">
        @csrf
        <input type="hidden" name="class_id" value="{{ request('class_id') }}">
        <input type="hidden" name="session_id" value="{{ request('session_id') }}">
        <input type="hidden" name="fee_month" value="{{ $fee_month }}">
        <input type="hidden" name="due_date" value="{{ $due_date }}">
        <input type="hidden" name="include_previous_balance" value="{{ $include_previous_balance ? 1 : 0 }}">

        <button type="submit" class="btn-save w-100 justify-content-center">
            <i class="fas fa-check-circle"></i>
            Confirm &amp; Generate {{ $toGenerate->count() }} Voucher(s)
        </button>
    </form>
@else
    <div class="alert alert-warning mt-3">
        <i class="fas fa-exclamation-triangle me-1"></i>
        Nothing to generate — every student either already has a voucher for this month, or has no fee configured.
    </div>
@endif

@endsection
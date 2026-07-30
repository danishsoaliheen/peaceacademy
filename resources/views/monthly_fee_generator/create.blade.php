@extends('layouts.dashboard')

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-cogs me-2" style="opacity:.8;"></i>Monthly Fee Generator</h2>
            <p>Bulk-generate this month's fee vouchers for an entire class in one go</p>
        </div>
        <a href="{{ route('fee-vouchers.index') }}" class="btn-hero-ghost">
            <i class="fas fa-file-invoice-dollar"></i> View Vouchers
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <strong><i class="fas fa-exclamation-triangle me-1"></i>Please fix the following:</strong>
        <ul class="mb-0 mt-1 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">

    <div class="col-lg-7">

        <div class="section-card card">
            <div class="card-header">
                <span class="s-icon" style="background:#3b82f6;"><i class="fas fa-sliders-h"></i></span>
                <h6>Generation Settings</h6>
            </div>
            <div class="card-body">

                {{-- Posts to preview() first — nothing is written to the
                     database until you confirm on the next screen. --}}
                <form method="POST" action="{{ route('monthly-fee-generator.preview') }}">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Session</label>
                            <select name="session_id" class="form-select" required>
                                <option value="">Select Session</option>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" {{ old('session_id') == $session->id ? 'selected' : '' }}>
                                        {{ $session->session_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fee Month</label>
                            <input type="month" name="fee_month" class="form-control"
                                   value="{{ old('fee_month', now()->format('Y-m')) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control"
                                   value="{{ old('due_date', now()->addDays(10)->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-12">
                            <div class="form-check p-3" style="background:#f8fafc; border-radius:8px;">
                                <input type="checkbox" name="include_previous_balance" value="1"
                                       class="form-check-input" id="prevBalCheck"
                                       {{ old('include_previous_balance') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="prevBalCheck" style="font-size:.86rem;">
                                    Include previous outstanding balance
                                </label>
                                <div class="text-muted small mt-1">
                                    Adds each student's unpaid balance from earlier, overdue vouchers as an extra
                                    line on this month's voucher — so the voucher shows one combined payable amount.
                                </div>
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="btn-save w-100 justify-content-center mt-4">
                        <i class="fas fa-eye"></i> Preview Before Generating
                    </button>

                </form>

            </div>
        </div>

    </div>

    <div class="col-lg-5">
        <div class="sticky-side">

            {{-- How it works — answers "how does it pick previous balance" directly in the UI --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#0891b2;"><i class="fas fa-info-circle"></i></span>
                    <h6>How the Generator Works</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-3" style="padding-left:18px; font-size:.83rem; line-height:1.9; color:#334155;">
                        <li>Finds every <strong>active enrollment</strong> in the selected class + session.</li>
                        <li>Looks up the class's <strong>Fee Structure</strong> (sum of all its fee-type amounts). If none is set, it falls back to the student's individual <code>monthly_fee</code> on their enrollment record.</li>
                        <li><strong>Skips</strong> any student who already has a monthly voucher for that exact month — running the generator twice never creates duplicates.</li>
                        <li>Subtracts each student's <strong>per-enrollment discount</strong> to get the payable amount.</li>
                        <li>If "Include previous balance" is checked, adds up the <code>balance_amount</code> of every one of that student's <strong>unpaid/partial vouchers due before this month</strong>, and appends it as one extra line — <em>"Previous outstanding balance (b/f)"</em> — on the same voucher.</li>
                    </ol>
                    <div class="alert alert-info mb-0" style="font-size:.78rem; padding:10px 14px;">
                        <i class="fas fa-lightbulb me-1"></i>
                        Previous balance is calculated fresh every time from actual voucher records —
                        it isn't stored anywhere separately, so it's always accurate even if a payment
                        was recorded five minutes ago.
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

@endsection
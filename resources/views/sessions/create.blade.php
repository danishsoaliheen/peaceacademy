@extends('layouts.dashboard')

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-calendar-plus me-2" style="opacity:.8;"></i>New Academic Session</h2>
            <p>Create a new academic year or term</p>
        </div>
        <a href="{{ route('sessions.index') }}" class="btn-hero-ghost">
            <i class="fas fa-arrow-left"></i> Back to Sessions
        </a>
    </div>
</div>

{{-- ── Validation errors ────────────────────────────────────────────────── --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- ── Form ────────────────────────────────────────────────────────── --}}
    <div class="col-lg-7">

        <form method="POST" action="{{ route('sessions.store') }}">
            @csrf

            {{-- Session Details --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#3b82f6;"><i class="fas fa-calendar-alt"></i></span>
                    <h6>Session Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">
                                Session Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="session_name"
                                   class="form-control @error('session_name') is-invalid @enderror"
                                   value="{{ old('session_name') }}"
                                   placeholder="e.g. 2025-2026"
                                   required>
                            <div class="form-text" style="font-size:.76rem;">
                                Use the academic year format, e.g. 2025-2026
                            </div>
                            @error('session_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date"
                                   name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date"
                                   name="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#64748b;"><i class="fas fa-toggle-on"></i></span>
                    <h6>Session Status</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch" style="padding-left:3rem;">
                        <input class="form-check-input" type="checkbox"
                               name="is_active" id="is_active" value="1"
                               {{ old('is_active') ? 'checked' : '' }}
                               style="width:2.4rem;height:1.25rem;">
                        <label class="form-check-label fw-semibold ms-2" for="is_active"
                               style="line-height:1.25rem;">
                            Set as Current Active Session
                        </label>
                    </div>
                    <div class="mt-2 p-2 rounded-2"
                         style="background:#fffbeb;border:1px solid #fde68a;font-size:.78rem;color:#92400e;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Checking this box alone does <strong>not</strong> deactivate other sessions.
                        Use the <strong>Set Current</strong> button on the sessions list to properly switch the active session.
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save Session
                </button>
                <a href="{{ route('sessions.index') }}"
                   class="btn btn-outline-secondary"
                   style="border-radius:9px;font-weight:600;padding:11px 22px;">
                    Cancel
                </a>
            </div>

        </form>

    </div>

    {{-- ── Tips panel ───────────────────────────────────────────────────── --}}
    <div class="col-lg-5">
        <div class="sticky-side">

            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#8b5cf6;"><i class="fas fa-lightbulb"></i></span>
                    <h6>Tips</h6>
                </div>
                <div class="card-body" style="font-size:.84rem;color:#475569;">

                    <div class="d-flex gap-2 mb-3">
                        <div style="width:24px;height:24px;background:#eff6ff;border-radius:6px;
                                    display:flex;align-items:center;justify-content:center;
                                    flex-shrink:0;color:#3b82f6;font-size:.7rem;font-weight:700;">1</div>
                        <div>
                            <strong style="color:#1e293b;">Session Name</strong>
                            <p class="mb-0 mt-1">Use a consistent format such as <code>2025-2026</code> or <code>Spring 2026</code> so reports sort correctly.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <div style="width:24px;height:24px;background:#eff6ff;border-radius:6px;
                                    display:flex;align-items:center;justify-content:center;
                                    flex-shrink:0;color:#3b82f6;font-size:.7rem;font-weight:700;">2</div>
                        <div>
                            <strong style="color:#1e293b;">Dates</strong>
                            <p class="mb-0 mt-1">Setting dates enables duration tracking on the sessions list. Leave blank if dates are not yet confirmed.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <div style="width:24px;height:24px;background:#eff6ff;border-radius:6px;
                                    display:flex;align-items:center;justify-content:center;
                                    flex-shrink:0;color:#3b82f6;font-size:.7rem;font-weight:700;">3</div>
                        <div>
                            <strong style="color:#1e293b;">Active Session</strong>
                            <p class="mb-0 mt-1">Only one session should be active at a time. Use <em>Set Current</em> on the list page to switch cleanly — it deactivates all others automatically.</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection

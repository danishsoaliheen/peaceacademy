@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.page-hero {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-radius: 12px;
    color: #fff;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
    pointer-events: none;
}
.page-hero h2 { font-size: 1.35rem; font-weight: 700; margin: 0 0 4px; }
.page-hero p  { margin: 0; opacity: .7; font-size: .85rem; }

.section-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    margin-bottom: 20px;
    overflow: hidden;
}
.section-card .card-header {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-card .card-header .s-icon {
    width: 28px; height: 28px;
    border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; color: #fff; flex-shrink: 0;
}
.section-card .card-header h6 { margin: 0; font-weight: 700; font-size: .9rem; color: #1e293b; }
.section-card .card-body { padding: 20px; }

.form-label { font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: 5px; }
.form-control, .form-select { border-radius: 8px !important; font-size: .875rem; border-color: #e2e8f0; padding: 8px 12px; }
.form-control:focus, .form-select:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59,130,246,.12) !important; }

.btn-save {
    background: linear-gradient(135deg, #1e293b, #334155);
    color: #fff; border: none; border-radius: 9px;
    padding: 11px 22px; font-weight: 700; font-size: .9rem;
    display: inline-flex; align-items: center; gap: 7px;
    transition: opacity .15s; cursor: pointer; width: 100%;
    justify-content: center;
}
.btn-save:hover { opacity: .86; color: #fff; }

.info-table tr { border-bottom: 1px solid #f1f5f9; }
.info-table tr:last-child { border-bottom: none; }
.info-table td { padding: 9px 20px; font-size: .855rem; vertical-align: middle; }
.info-table td.lbl { width: 42%; color: #64748b; font-weight: 600; }
.info-table td.val { color: #1e293b; font-weight: 500; }

@media (min-width: 992px) { .sticky-side { position: sticky; top: 20px; } }
</style>

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h2><i class="fas fa-calendar-edit me-2" style="opacity:.8;"></i>Edit Session</h2>
            <p>Update details for <strong style="opacity:1;">{{ $session->session_name }}</strong></p>
        </div>

        <div class="d-flex gap-2 flex-wrap align-items-center">

            @if($session->is_active)
                <span class="badge" style="background:#22c55e; font-size:.75rem; padding:5px 12px; border-radius:6px;">
                    <i class="fas fa-circle me-1" style="font-size:.45rem; vertical-align:middle;"></i>Current Session
                </span>
            @else
                <form action="{{ route('sessions.set-active', $session->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="btn btn-sm btn-outline-light" style="border-radius:8px; font-weight:600;"
                            onclick="return confirm('Set \'{{ addslashes($session->session_name) }}\' as the current session?')">
                        <i class="fas fa-check-circle me-1"></i> Set as Current
                    </button>
                </form>
            @endif

            <a href="{{ route('sessions.index') }}"
               class="btn btn-sm btn-outline-light" style="border-radius:8px; font-weight:600;">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>

        </div>
    </div>
</div>

{{-- ── Alerts ───────────────────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:8px; font-size:.875rem;">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:8px; font-size:.875rem;">
        <strong><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('sessions.update', $session->id) }}">
@csrf
@method('PUT')

<div class="row g-4">

    {{-- ── Left: form fields ───────────────────────────────────────────── --}}
    <div class="col-lg-7">

        {{-- Session details --}}
        <div class="section-card card">
            <div class="card-header">
                <span class="s-icon" style="background:#f59e0b;"><i class="fas fa-calendar-alt"></i></span>
                <h6>Session Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label">Session Name <span class="text-danger">*</span></label>
                        <input type="text" name="session_name"
                               class="form-control @error('session_name') is-invalid @enderror"
                               value="{{ old('session_name', $session->session_name) }}"
                               required>
                        @error('session_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', $session->start_date?->format('Y-m-d')) }}">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', $session->end_date?->format('Y-m-d')) }}">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                           {{ old('is_active', $session->is_active) ? 'checked' : '' }}
                           style="width:2.4rem; height:1.25rem;">
                    <label class="form-check-label fw-semibold ms-2" for="is_active"
                           style="line-height:1.25rem;">
                        Active Session
                    </label>
                </div>
                @if($session->is_active)
                    <div class="mt-2 p-2 rounded-2"
                         style="background:#f0fdf4; border:1px solid #bbf7d0; font-size:.78rem; color:#166534;">
                        <i class="fas fa-info-circle me-1"></i>
                        This is currently the active session. Unchecking will mark it inactive — ensure another session is set as current.
                    </div>
                @endif
            </div>
        </div>

        {{-- Enrolled students info --}}
        @if($session->enrolled_students_count > 0)
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#3b82f6;"><i class="fas fa-users"></i></span>
                    <h6>Enrolled Students</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div style="font-size:.86rem; color:#475569;">
                        <strong style="font-size:1.3rem; color:#1e293b;">{{ $session->enrolled_students_count }}</strong>
                        &nbsp;active student(s) enrolled in this session
                    </div>
                    <a href="{{ route('students.index') }}?session_id={{ $session->id }}"
                       class="btn btn-sm"
                       style="background:#eff6ff; color:#1d4ed8; border:none; border-radius:6px; font-weight:600; font-size:.78rem;">
                        <i class="fas fa-eye me-1"></i> View All
                    </a>
                </div>
            </div>
        @endif

    </div>

    {{-- ── Right: save + info + danger ────────────────────────────────── --}}
    <div class="col-lg-5">
        <div class="sticky-side">

            {{-- Save --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#10b981;"><i class="fas fa-save"></i></span>
                    <h6>Save Changes</h6>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Update Session
                    </button>
                    <a href="{{ route('sessions.index') }}"
                       class="btn btn-outline-secondary w-100 mt-2"
                       style="border-radius:9px; font-weight:600;">
                        Cancel
                    </a>
                </div>
            </div>

            {{-- Overview --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#0f172a;"><i class="fas fa-info-circle"></i></span>
                    <h6>Session Overview</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-borderless info-table mb-0">
                        <tbody>
                            <tr>
                                <td class="lbl">Session ID</td>
                                <td class="val">
                                    <code style="font-size:.8rem; background:#f8fafc; padding:2px 6px; border-radius:4px;">#{{ $session->id }}</code>
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Status</td>
                                <td class="val">
                                    @if($session->is_active)
                                        <span class="badge" style="background:#dcfce7; color:#166534; font-size:.72rem; padding:3px 8px; border-radius:4px;">Active</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:.72rem; padding:3px 8px; border-radius:4px;">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Students</td>
                                <td class="val fw-bold">{{ $session->enrolled_students_count }}</td>
                            </tr>
                            @if($session->start_date && $session->end_date)
                                <tr>
                                    <td class="lbl">Duration</td>
                                    <td class="val" style="color:#475569;">
                                        {{ $session->start_date->diffInMonths($session->end_date) }} months
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Danger zone (only if inactive and no students) --}}
            @if(!$session->is_active && $session->enrolled_students_count === 0)
                <div class="section-card card" style="border: 1.5px solid #fca5a5 !important;">
                    <div class="card-header" style="background:#fef2f2 !important; border-bottom-color:#fca5a5 !important;">
                        <span class="s-icon" style="background:#dc2626;"><i class="fas fa-trash-alt"></i></span>
                        <h6 style="color:#991b1b;">Danger Zone</h6>
                    </div>
                    <div class="card-body">
                        <p style="font-size:.82rem; color:#475569; margin-bottom:12px;">
                            No students enrolled and session is inactive. You can permanently delete it.
                        </p>
                        <form action="{{ route('sessions.destroy', $session->id) }}"
                              method="POST"
                              onsubmit="return confirm('Permanently delete \'{{ addslashes($session->session_name) }}\'? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm w-100"
                                    style="background:#fee2e2; color:#991b1b; border:none; border-radius:6px; font-weight:600; padding:8px;">
                                <i class="fas fa-trash me-1"></i> Delete This Session
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection

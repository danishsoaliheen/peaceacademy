@extends('layouts.dashboard')

@section('content')
<style>
.user-hero { background:linear-gradient(135deg,#1e293b 0%,#334155 100%); border-radius:12px; color:#fff; padding:24px 30px; margin-bottom:22px; }
.section-card { border:none; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,.08); margin-bottom:20px; overflow:hidden; }
.section-card .card-header { background:#f8fafc; border-bottom:2px solid #e2e8f0; padding:12px 20px; display:flex; align-items:center; gap:8px; }
.section-card .card-body { padding:20px; }
.form-label { font-size:.8rem; font-weight:600; color:#475569; margin-bottom:5px; }
.form-control,.form-select { border-radius:8px; font-size:.875rem; border-color:#e2e8f0; padding:8px 12px; }
.btn-save { background:linear-gradient(135deg,#1e293b,#334155); color:#fff; border:none; border-radius:9px; padding:11px 22px; font-weight:700; }
</style>

<div class="user-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div><h2 class="mb-1" style="font-size:1.25rem;font-weight:700;"><i class="fas fa-user-plus me-2"></i>New User</h2><p class="mb-0" style="opacity:.68;font-size:.82rem;">Create a staff account for Peace Academy ERP</p></div>
    <a href="{{ route('users.index') }}" class="btn-hero-ghost"><i class="fas fa-arrow-left"></i> Back to Users</a>
</div>

@if($errors->any())<div class="alert alert-danger"><strong>Please fix the following:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('users.store') }}">
@csrf
<div class="row g-4">
    <div class="col-lg-8">
        <div class="section-card card">
            <div class="card-header"><span class="s-icon" style="background:#3b82f6;width:27px;height:27px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;color:#fff;"><i class="fas fa-user"></i></span><strong>Account Information</strong></div>
            <div class="card-body"><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label><input name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" minlength="8" required><small class="text-muted">Minimum 8 characters.</small></div>
                <div class="col-md-6"><label class="form-label">Confirm Password <span class="text-danger">*</span></label><input type="password" name="password_confirmation" class="form-control" minlength="8" required></div>
            </div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="section-card card">
            <div class="card-header"><span class="s-icon" style="background:#8b5cf6;width:27px;height:27px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;color:#fff;"><i class="fas fa-shield-halved"></i></span><strong>Access</strong></div>
            <div class="card-body">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <option value="">— Select Role —</option>
                    @foreach($roles as $key => $label)<option value="{{ $key }}" @selected(old('role') === $key)>{{ $label }}</option>@endforeach
                </select>
                <small class="text-muted d-block mt-2">Role controls which protected areas the user can access.</small>
                <div class="alert alert-info mt-3 mb-0" style="font-size:.8rem;"><i class="fas fa-circle-info me-1"></i>All newly created accounts start as active.</div>
            </div>
        </div>
        <div class="d-grid gap-2"><button class="btn-save"><i class="fas fa-save me-1"></i> Create User</button><a href="{{ route('users.index') }}" class="btn btn-light" style="border-radius:9px;">Cancel</a></div>
    </div>
</div>
</form>
@endsection
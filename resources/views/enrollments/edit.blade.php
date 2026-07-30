@extends('layouts.dashboard')

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-user-edit me-2" style="opacity:.8;"></i>Edit Enrollment</h2>
            <p>{{ $enrollment->student->student_name ?? 'Student' }} — {{ $enrollment->student->admission_no ?? '' }}</p>
        </div>
        <a href="{{ route('enrollments.index') }}" class="btn-hero-ghost">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

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

<form action="{{ route('enrollments.update', $enrollment->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">

        <div class="col-lg-8">

            {{-- Enrollment Details --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="s-icon" style="background:#3b82f6;"><i class="fas fa-clipboard-list"></i></span>
                    <h6>Enrollment Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select name="class_id" class="form-select" required>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ $enrollment->class_id == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Session</label>
                            <select name="session_id" class="form-select" required>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" {{ $enrollment->session_id == $session->id ? 'selected' : '' }}>
                                        {{ $session->session_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Roll No</label>
                            <input type="text" name="roll_no" class="form-control"
                                   value="{{ old('roll_no', $enrollment->roll_no) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Enrollment Date</label>
                            <input type="date" name="enrollment_date" class="form-control"
                                   value="{{ old('enrollment_date', $enrollment->enrollment_date ? \Carbon\Carbon::parse($enrollment->enrollment_date)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Monthly Fee</label>
                            <input type="number" step="0.01" min="0" name="monthly_fee" class="form-control"
                                   value="{{ old('monthly_fee', $enrollment->monthly_fee) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Discount Amount</label>
                            <input type="number" step="0.01" min="0" name="discount_amount" class="form-control"
                                   value="{{ old('discount_amount', $enrollment->discount_amount) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $enrollment->notes) }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="sticky-side">

                {{-- Status --}}
                <div class="section-card card">
                    <div class="card-header">
                        <span class="s-icon" style="background:#64748b;"><i class="fas fa-toggle-on"></i></span>
                        <h6>Status</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Enrollment Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active"     {{ $enrollment->status === 'active'     ? 'selected' : '' }}>Active</option>
                            <option value="inactive"   {{ $enrollment->status === 'inactive'   ? 'selected' : '' }}>Inactive</option>
                            <option value="left"       {{ $enrollment->status === 'left'       ? 'selected' : '' }}>Left</option>
                            <option value="passed_out" {{ $enrollment->status === 'passed_out' ? 'selected' : '' }}>Passed Out</option>
                        </select>
                        <small class="text-muted d-block mt-2">
                            "Active" keeps this enrollment counted in class rosters, dashboards and fee generation.
                        </small>
                    </div>
                </div>

                {{-- Student summary --}}
                <div class="section-card card">
                    <div class="card-header">
                        <span class="s-icon" style="background:#8b5cf6;"><i class="fas fa-user"></i></span>
                        <h6>Student</h6>
                    </div>
                    <div class="card-body text-center">
                        @if($enrollment->student && $enrollment->student->photo_url)
                            <img src="{{ $enrollment->student->photo_url }}" alt=""
                                 style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #e2e8f0;">
                        @else
                            <div style="width:72px;height:72px;border-radius:50%;background:#3b82f6;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:700;">
                                {{ $enrollment->student ? strtoupper(substr($enrollment->student->student_name,0,1)) : '?' }}
                            </div>
                        @endif
                        <div class="fw-bold mt-2">{{ $enrollment->student->student_name ?? '—' }}</div>
                        <div class="text-muted small">{{ $enrollment->student->admission_no ?? '' }}</div>
                        @if($enrollment->student)
                            <a href="{{ route('students.show', $enrollment->student->id) }}" class="btn btn-sm btn-outline-primary mt-2" style="border-radius:8px;">
                                <i class="fas fa-eye me-1"></i> View Profile
                            </a>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn-save w-100 justify-content-center">
                    <i class="fas fa-save"></i> Save Changes
                </button>

            </div>
        </div>

    </div>
</form>

@endsection
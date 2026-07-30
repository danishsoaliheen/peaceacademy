@extends('layouts.dashboard')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Add New Class</h2>
            <p class="text-muted mb-0">Create a new class for the school</p>
        </div>
        <a href="{{ route('classes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Classes
        </a>
    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-school me-2"></i>Class Details</h5>
                </div>

                <div class="card-body p-4">

                    <form method="POST" action="{{ route('classes.store') }}">
                        @csrf

                        {{-- Class Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Class Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="class_name"
                                   class="form-control form-control-lg @error('class_name') is-invalid @enderror"
                                   value="{{ old('class_name') }}"
                                   placeholder="e.g. Class 1, KG-I, Reception"
                                   required>
                            @error('class_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Class Code --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Class Code
                                <small class="text-muted fw-normal">(short label, e.g. C1, KG1)</small>
                            </label>
                            <input type="text"
                                   name="class_code"
                                   class="form-control @error('class_code') is-invalid @enderror"
                                   value="{{ old('class_code') }}"
                                   placeholder="e.g. C1"
                                   maxlength="20">
                            @error('class_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Class Order --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Display Order <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="class_order"
                                   class="form-control @error('class_order') is-invalid @enderror"
                                   value="{{ old('class_order', $nextOrder) }}"
                                   min="1"
                                   required>
                            <div class="form-text">
                                Controls the order in which classes appear in dropdowns and tables.
                                You can also drag to reorder from the classes list.
                            </div>
                            @error('class_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Active Status --}}
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="is_active"
                                       id="is_active"
                                       value="1"
                                       {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    Active Class
                                </label>
                            </div>
                            <div class="form-text">
                                Inactive classes will not appear in enrollment or fee voucher forms.
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Class
                            </button>
                            <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection

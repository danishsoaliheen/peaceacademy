@extends('layouts.dashboard')

@section('content')

<div class="container-fluid mt-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">
                Student Admission Form
            </h2>

            <p class="text-muted mb-0">
                Add new student admission details
            </p>
        </div>

        <a href="{{ route('pa_students.index') }}"
           class="btn btn-secondary">

            <i class="fa fa-arrow-left"></i>
            Back to Students

        </a>

    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())

        <div class="alert alert-danger">

            <strong>
                Please fix the following errors:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form method="POST"
          action="{{ route('pa_students.store') }}"
          enctype="multipart/form-data">

        @csrf

        <div class="row">

            {{-- LEFT SIDE --}}
            <div class="col-lg-8">

                {{-- Basic Information --}}
                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">
                            Basic Information
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            {{-- Admission No --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Admission No
                                </label>

                                <input type="text"
                                       class="form-control"
                                       value="Auto Generated"
                                       readonly>

                            </div>

                            {{-- Admission Date --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Admission Date
                                </label>

                                <input type="date"
                                       name="admission_date"
                                       class="form-control"
                                       value="{{ old('admission_date', date('Y-m-d')) }}">

                            </div>

                            {{-- Gender --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Gender
                                </label>

                                <select name="gender"
                                        class="form-select">

                                    <option value="">
                                        Select Gender
                                    </option>

                                    <option value="Male"
                                        {{ old('gender') == 'Male' ? 'selected' : '' }}>
                                        Male
                                    </option>

                                    <option value="Female"
                                        {{ old('gender') == 'Female' ? 'selected' : '' }}>
                                        Female
                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="row">

                            {{-- Student Name --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Student Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="student_name"
                                       class="form-control"
                                       value="{{ old('student_name') }}"
                                       required>

                            </div>

                            {{-- Father Name --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Father Name
                                </label>

                                <input type="text"
                                       name="father_name"
                                       class="form-control"
                                       value="{{ old('father_name') }}">

                            </div>

                            {{-- Mother Name --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Mother Name
                                </label>

                                <input type="text"
                                       name="mother_name"
                                       class="form-control"
                                       value="{{ old('mother_name') }}">

                            </div>

                        </div>

                        <div class="row">

                            {{-- Date of Birth --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Date of Birth
                                </label>

                                <input type="date"
                                       name="date_of_birth"
                                       class="form-control"
                                       value="{{ old('date_of_birth') }}">

                            </div>

                            {{-- Blood Group --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Blood Group
                                </label>

                                <select name="blood_group"
                                        class="form-select">

                                    <option value="">
                                        Select Blood Group
                                    </option>

                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>

                                </select>

                            </div>

                            {{-- Religion --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Religion
                                </label>

                                <input type="text"
                                       name="religion"
                                       class="form-control"
                                       value="{{ old('religion') }}">

                            </div>

                        </div>

                        <div class="row">

                            {{-- B Form --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">
                                    B-Form Number
                                </label>

                                <input type="text"
                                       name="b_form_no"
                                       class="form-control"
                                       value="{{ old('b_form_no') }}">

                            </div>

                            {{-- Previous School --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">
                                    Previous School
                                </label>

                                <input type="text"
                                       name="previous_school"
                                       class="form-control"
                                       value="{{ old('previous_school') }}">

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Guardian Information --}}
                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-header bg-success text-white">

                        <h5 class="mb-0">
                            Guardian Information
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            {{-- Guardian Name --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Guardian Name
                                </label>

                                <input type="text"
                                       name="guardian_name"
                                       class="form-control"
                                       value="{{ old('guardian_name') }}">

                            </div>

                            {{-- Guardian Relation --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Guardian Relation
                                </label>

                                <input type="text"
                                       name="guardian_relation"
                                       class="form-control"
                                       value="{{ old('guardian_relation') }}">

                            </div>

                            {{-- Guardian Mobile --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Guardian Mobile
                                </label>

                                <input type="text"
                                       name="guardian_mobile"
                                       class="form-control"
                                       value="{{ old('guardian_mobile') }}">

                            </div>

                        </div>

                        <div class="row">

                            {{-- Mobile --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Mobile Number
                                </label>

                                <input type="text"
                                       name="mobile_no"
                                       class="form-control"
                                       value="{{ old('mobile_no') }}">

                            </div>

                            {{-- WhatsApp --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    WhatsApp Number
                                </label>

                                <input type="text"
                                       name="whatsapp_no"
                                       class="form-control"
                                       value="{{ old('whatsapp_no') }}">

                            </div>

                            {{-- Occupation --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Father Occupation
                                </label>

                                <input type="text"
                                       name="father_occupation"
                                       class="form-control"
                                       value="{{ old('father_occupation') }}">

                            </div>

                        </div>

                        {{-- Address --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Address
                            </label>

                            <textarea name="address"
                                      class="form-control"
                                      rows="3">{{ old('address') }}</textarea>

                        </div>

                    </div>

                </div>

            </div>

            {{-- RIGHT SIDE --}}
            <div class="col-lg-4">

                {{-- Enrollment Information --}}
                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-header bg-dark text-white">

                        <h5 class="mb-0">
                            Enrollment Information
                        </h5>

                    </div>

                    <div class="card-body">

                        {{-- Class --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Class <span class="text-danger">*</span>
                            </label>

                            <select name="class_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    Select Class
                                </option>

                                @foreach($classes as $class)

                                    <option value="{{ $class->id }}"
                                        {{ old('class_id') == $class->id ? 'selected' : '' }}>

                                        {{ $class->class_name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- Session --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Academic Session <span class="text-danger">*</span>
                            </label>

                            <select name="session_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    Select Session
                                </option>

                                @foreach($sessions as $session)

                                    <option value="{{ $session->id }}"
                                        {{ old('session_id') == $session->id ? 'selected' : '' }}>

                                        {{ $session->session_name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- Previous Class --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Previous Class
                            </label>

                            <input type="text"
                                   name="previous_class"
                                   class="form-control"
                                   value="{{ old('previous_class') }}">

                        </div>

                    </div>

                </div>

                {{-- Student Photo --}}
                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-header bg-info text-white">

                        <h5 class="mb-0">
                            Student Photo
                        </h5>

                    </div>

                    <div class="card-body text-center">

                        <img src="https://placehold.co/200x200"
                             class="img-fluid rounded mb-3 border"
                             style="max-height:200px; object-fit:cover;">

                        <input type="file"
                               name="student_image"
                               class="form-control">

                    </div>

                </div>

                {{-- Save Button --}}
                <div class="d-grid">

                    <button type="submit"
                            class="btn btn-primary btn-lg">

                        Save Student Admission

                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

@endsection
@extends('layouts.dashboard')

@section('content')

<div class="container-fluid mt-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                Edit Student Record
            </h2>

            <p class="text-muted mb-0">
                Update student admission and profile information
            </p>

        </div>

        <a href="{{ route('pa_students.index') }}"
           class="btn btn-secondary">

            <i class="fa fa-arrow-left"></i>
            Back to Students

        </a>

    </div>

    {{-- Success Message --}}
    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif

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
          action="{{ route('pa_students.update', $student->id) }}"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

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
                                    Admission / GR Number
                                </label>

                                <input type="text"
                                       name="admission_no"
                                       class="form-control"
                                       value="{{ old('admission_no', $student->admission_no) }}">

                            </div>

                            {{-- Admission Date --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Admission Date
                                </label>

                                <input type="date"
                                       name="admission_date"
                                       class="form-control"
                                       value="{{ old('admission_date', $student->admission_date) }}">

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
                                        {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>
                                        Male
                                    </option>

                                    <option value="Female"
                                        {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>
                                        Female
                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="row">

                            {{-- Student Name --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Student Name
                                </label>

                                <input type="text"
                                       name="student_name"
                                       class="form-control"
                                       value="{{ old('student_name', $student->student_name) }}">

                            </div>

                            {{-- Father Name --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Father Name
                                </label>

                                <input type="text"
                                       name="father_name"
                                       class="form-control"
                                       value="{{ old('father_name', $student->father_name) }}">

                            </div>

                            {{-- Mother Name --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Mother Name
                                </label>

                                <input type="text"
                                       name="mother_name"
                                       class="form-control"
                                       value="{{ old('mother_name', $student->mother_name) }}">

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
                                       value="{{ old('date_of_birth', $student->date_of_birth) }}">

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

                                    <option value="A+" {{ old('blood_group', $student->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('blood_group', $student->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('blood_group', $student->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('blood_group', $student->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('blood_group', $student->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('blood_group', $student->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ old('blood_group', $student->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('blood_group', $student->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>

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
                                       value="{{ old('religion', $student->religion) }}">

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
                                       value="{{ old('b_form_no', $student->b_form_no) }}">

                            </div>

                            {{-- Previous School --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">
                                    Previous School
                                </label>

                                <input type="text"
                                       name="previous_school"
                                       class="form-control"
                                       value="{{ old('previous_school', $student->previous_school) }}">

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
                                       value="{{ old('guardian_name', $student->guardian_name) }}">

                            </div>

                            {{-- Guardian Relation --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Guardian Relation
                                </label>

                                <input type="text"
                                       name="guardian_relation"
                                       class="form-control"
                                       value="{{ old('guardian_relation', $student->guardian_relation) }}">

                            </div>

                            {{-- Guardian Mobile --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Guardian Mobile
                                </label>

                                <input type="text"
                                       name="guardian_mobile"
                                       class="form-control"
                                       value="{{ old('guardian_mobile', $student->guardian_mobile) }}">

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
                                       value="{{ old('mobile_no', $student->mobile_no) }}">

                            </div>

                            {{-- WhatsApp --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    WhatsApp Number
                                </label>

                                <input type="text"
                                       name="whatsapp_no"
                                       class="form-control"
                                       value="{{ old('whatsapp_no', $student->whatsapp_no) }}">

                            </div>

                            {{-- Occupation --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label fw-bold">
                                    Father Occupation
                                </label>

                                <input type="text"
                                       name="father_occupation"
                                       class="form-control"
                                       value="{{ old('father_occupation', $student->father_occupation) }}">

                            </div>

                        </div>

                        {{-- Address --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Address
                            </label>

                            <textarea name="address"
                                      class="form-control"
                                      rows="3">{{ old('address', $student->address) }}</textarea>

                        </div>

                    </div>

                </div>

            </div>

            {{-- RIGHT SIDE --}}
            <div class="col-lg-4">

                {{-- Student Photo --}}
                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-header bg-info text-white">

                        <h5 class="mb-0">
                            Student Photo
                        </h5>

                    </div>

                    <div class="card-body text-center">

                        @if($student->student_image)

                            <img src="{{ asset('uploads/students/' . $student->student_image) }}"
                                 class="img-fluid rounded border mb-3"
                                 style="max-height:220px; object-fit:cover;">

                        @else

                            <img src="https://placehold.co/250x250"
                                 class="img-fluid rounded border mb-3">

                        @endif

                        <input type="file"
                               name="student_image"
                               class="form-control">

                    </div>

                </div>

                {{-- Status --}}
                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-header bg-dark text-white">

                        <h5 class="mb-0">
                            Status
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="form-check form-switch">

                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ $student->is_active ? 'checked' : '' }}>

                            <label class="form-check-label">
                                Active Student
                            </label>

                        </div>

                    </div>

                </div>

                {{-- Submit Buttons --}}
                <div class="d-grid gap-2">

                    <button type="submit"
                            class="btn btn-primary btn-lg">

                        Update Student Record

                    </button>

                    <a href="{{ route('pa_students.show', $student->id) }}"
                       class="btn btn-info">

                        View Profile

                    </a>

                </div>

            </div>

        </div>

    </form>

</div>

@endsection
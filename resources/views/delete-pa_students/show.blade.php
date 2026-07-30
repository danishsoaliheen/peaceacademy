@extends('layouts.dashboard')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                Student Profile
            </h2>

            <p class="text-muted mb-0">
                Complete student information and enrollment history
            </p>

        </div>

        <div>

            <a href="{{ route('pa_students.edit', $student->id) }}"
               class="btn btn-warning">

                <i class="fas fa-edit me-1"></i>
                Edit Student

            </a>

            <a href="{{ route('pa_students.index') }}"
               class="btn btn-secondary">

                Back

            </a>

        </div>

    </div>

    <div class="row">

        {{-- LEFT SIDE --}}
        <div class="col-lg-4">

            {{-- Profile Card --}}
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-body text-center">

                    {{-- Student Photo --}}
                    @if($student->student_image)

                        <img src="{{ asset('storage/students/' . $student->student_image) }}"
                             class="rounded-circle border shadow-sm mb-3"
                             width="170"
                             height="170"
                             style="object-fit: cover;">

                    @else

                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="width:170px;height:170px;font-size:50px;">

                            {{ strtoupper(substr($student->student_name,0,1)) }}

                        </div>

                    @endif

                    <h4 class="fw-bold mb-1">
                        {{ $student->student_name }}
                    </h4>

                    <p class="text-muted mb-3">
                        Admission No:
                        <strong>{{ $student->admission_no }}</strong>
                    </p>

                    @if($student->is_active)

                        <span class="badge bg-success px-3 py-2">
                            Active Student
                        </span>

                    @else

                        <span class="badge bg-danger px-3 py-2">
                            Inactive
                        </span>

                    @endif

                </div>

            </div>

            {{-- Contact Card --}}
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Contact Information
                    </h5>

                </div>

                <div class="card-body">

                    <table class="table table-borderless">

                        <tr>

                            <th width="40%">
                                Mobile
                            </th>

                            <td>
                                {{ $student->mobile_no ?? '-' }}
                            </td>

                        </tr>

                        <tr>

                            <th>
                                WhatsApp
                            </th>

                            <td>
                                {{ $student->whatsapp_no ?? '-' }}
                            </td>

                        </tr>

                        <tr>

                            <th>
                                Guardian Mobile
                            </th>

                            <td>
                                {{ $student->guardian_mobile ?? '-' }}
                            </td>

                        </tr>

                        <tr>

                            <th>
                                Address
                            </th>

                            <td>
                                {{ $student->address ?? '-' }}
                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

        {{-- RIGHT SIDE --}}
        <div class="col-lg-8">

            {{-- Basic Information --}}
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Basic Information
                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Student Name
                            </label>

                            <div class="fw-bold">
                                {{ $student->student_name ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Father Name
                            </label>

                            <div class="fw-bold">
                                {{ $student->father_name ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Mother Name
                            </label>

                            <div class="fw-bold">
                                {{ $student->mother_name ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Gender
                            </label>

                            <div class="fw-bold">
                                {{ ucfirst($student->gender ?? '-') }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Date of Birth
                            </label>

                            <div class="fw-bold">
                                {{ $student->date_of_birth ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Admission Date
                            </label>

                            <div class="fw-bold">
                                {{ $student->admission_date ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                B-Form No
                            </label>

                            <div class="fw-bold">
                                {{ $student->b_form_no ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Blood Group
                            </label>

                            <div class="fw-bold">
                                {{ $student->blood_group ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Religion
                            </label>

                            <div class="fw-bold">
                                {{ $student->religion ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Father Occupation
                            </label>

                            <div class="fw-bold">
                                {{ $student->father_occupation ?? '-' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Guardian Information --}}
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Guardian Information
                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Guardian Name
                            </label>

                            <div class="fw-bold">
                                {{ $student->guardian_name ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Guardian Relation
                            </label>

                            <div class="fw-bold">
                                {{ $student->guardian_relation ?? '-' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Academic Information --}}
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Academic Information
                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Previous School
                            </label>

                            <div class="fw-bold">
                                {{ $student->previous_school ?? '-' }}
                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted small">
                                Previous Class
                            </label>

                            <div class="fw-bold">
                                {{ $student->previous_class ?? '-' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Enrollment History --}}
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        Enrollment History
                    </h5>

                </div>

                <div class="card-body table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    Session
                                </th>

                                <th>
                                    Class
                                </th>

                                <th>
                                    Roll No
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Enrollment Date
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($enrollments as $enrollment)

                                <tr>

                                    <td>
                                        {{ $enrollment->session->session_name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $enrollment->class->class_name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $enrollment->roll_no ?? '-' }}
                                    </td>

                                    <td>

                                        @if($enrollment->status == 'active')

                                            <span class="badge bg-success">
                                                Active
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>

                                        @endif

                                    </td>

                                    <td>
                                        {{ $enrollment->enrollment_date ?? '-' }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5"
                                        class="text-center text-muted">

                                        No enrollment history found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
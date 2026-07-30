@extends('layouts.dashboard')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                Student Management
            </h2>

            <p class="text-muted mb-0">
                Manage admissions, profiles, classes and sessions
            </p>
        </div>

        <a href="{{ route('pa_students.create') }}"
           class="btn btn-primary">

            <i class="fas fa-plus-circle me-1"></i>
            New Admission

        </a>

    </div>

    {{-- Search Card --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('pa_students.index') }}">

                <div class="row g-2">

                    <div class="col-md-10">

                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control"
                               placeholder="Search by Admission No, Student Name, Father Name">

                    </div>

                    <div class="col-md-2 d-grid">

                        <button class="btn btn-dark">

                            <i class="fas fa-search me-1"></i>
                            Search

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- Success Message --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

        </div>

    @endif

    {{-- Students Table --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white border-0 py-3">

            <h5 class="mb-0 fw-bold">
                Students List
            </h5>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th width="60">#</th>

                        <th width="90">
                            Photo
                        </th>

                        <th>
                            Admission No
                        </th>

                        <th>
                            Student Details
                        </th>

                        <th>
                            Class
                        </th>

                        <th>
                            Session
                        </th>

                        <th>
                            Contact
                        </th>

                        <th>
                            Status
                        </th>

                        <th width="220">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($students as $index => $student)

                        @php
                            $enrollment = $student->enrollments->last();
                        @endphp

                        <tr>

                            <td>
                                {{ $students->firstItem() + $index }}
                            </td>

                            {{-- Photo --}}
                            <td>

                                @if($student->student_image)

                                    <img src="{{ asset('storage/students/' . $student->student_image) }}"
                                         width="55"
                                         height="55"
                                         class="rounded-circle border object-fit-cover">

                                @else

                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:55px;height:55px;">

                                        <strong>
                                            {{ strtoupper(substr($student->student_name,0,1)) }}
                                        </strong>

                                    </div>

                                @endif

                            </td>

                            {{-- Admission No --}}
                            <td>

                                <span class="fw-bold text-primary">

                                    {{ $student->admission_no }}

                                </span>

                            </td>

                            {{-- Student Info --}}
                            <td>

                                <div class="fw-bold">

                                    {{ $student->student_name }}

                                </div>

                                <small class="text-muted d-block">

                                    Father:
                                    {{ $student->father_name ?? 'N/A' }}

                                </small>

                                <small class="text-muted d-block">

                                    Gender:
                                    {{ ucfirst($student->gender ?? '-') }}

                                </small>

                            </td>

                            {{-- Class --}}
                            <td>

                                @if($enrollment && $enrollment->class)

                                    <span class="badge bg-success px-3 py-2">

                                        {{ $enrollment->class->class_name }}

                                    </span>

                                @else

                                    <span class="badge bg-secondary">

                                        Not Assigned

                                    </span>

                                @endif

                            </td>

                            {{-- Session --}}
                            <td>

                                @if($enrollment && $enrollment->session)

                                    <span class="badge bg-info text-dark px-3 py-2">

                                        {{ $enrollment->session->session_name }}

                                    </span>

                                @else

                                    -

                                @endif

                            </td>

                            {{-- Contact --}}
                            <td>

                                <div>

                                    {{ $student->mobile_no ?? '-' }}

                                </div>

                                <small class="text-muted">

                                    {{ $student->guardian_mobile ?? '' }}

                                </small>

                            </td>

                            {{-- Status --}}
                            <td>

                                @if($student->is_active)

                                    <span class="badge bg-success">

                                        Active

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Inactive

                                    </span>

                                @endif

                            </td>

                            {{-- Actions --}}
                            <td>

                                <div class="d-flex gap-1 flex-wrap">

                                    {{-- View --}}
                                    <a href="{{ route('pa_students.show', $student->id) }}"
                                       class="btn btn-sm btn-info text-white">

                                        View

                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('pa_students.edit', $student->id) }}"
                                       class="btn btn-sm btn-warning">

                                        Edit

                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('pa_students.destroy', $student->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure to delete this student?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-danger">

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9"
                                class="text-center py-5">

                                <h5 class="text-muted">

                                    No students found

                                </h5>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- Pagination --}}
    <div class="mt-4">

        {{ $students->withQueryString()->links() }}

    </div>

</div>

@endsection
@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">Enroll Student</div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('pa_enrollments.store') }}">
            @csrf

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Student</label>
                    <select name="student_id" class="form-control">
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->student_name }} ({{ $student->gr_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Class</label>
                    <select name="class_id" class="form-control">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Session</label>
                    <select name="session_id" class="form-control">
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">
                                {{ $session->session_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Enrollment Date</label>
                    <input type="date" name="enroll_date" class="form-control">
                </div>
            </div>

            <button class="btn btn-primary">Enroll</button>

        </form>

    </div>
</div>

@endsection
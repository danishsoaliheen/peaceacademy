@extends('layouts.app')

@section('content')

<h2>Enrollment List</h2>

<table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Class</th>
            <th>Session</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        @foreach($enrollments as $enroll)
            <tr>
                <td>{{ $enroll->id }}</td>
                <td>{{ $enroll->student->student_name ?? '' }}</td>
                <td>{{ $enroll->class->class_name ?? '' }}</td>
                <td>{{ $enroll->session->session_name ?? '' }}</td>
                <td>{{ $enroll->status }}</td>
                <td>{{ $enroll->enrollment_date }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
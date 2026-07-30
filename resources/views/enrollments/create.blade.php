@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html>

<head>

    <title>Student Enrollment</title>

    <style>

        body{
            font-family:Arial;
            margin:20px;
            background:#f5f5f5;
        }

        table{
            width:100%;
            background:white;
            border-collapse:collapse;
        }

        td{
            padding:10px;
            border:1px solid #ddd;
        }

        input,
        select{
            width:100%;
            padding:8px;
        }

        .btn{
            padding:10px 20px;
            background:#0d6efd;
            color:white;
            border:none;
            cursor:pointer;
        }

        .success{
            background:#d1e7dd;
            color:#0f5132;
            padding:10px;
            margin-bottom:20px;
        }

        .error{
            background:#f8d7da;
            color:#842029;
            padding:10px;
            margin-bottom:20px;
        }

    </style>

</head>

<body>

<h2>Student Enrollment</h2>

@if(session('success'))

    <div class="success">
        {{ session('success') }}
    </div>

@endif

@if(session('error'))

    <div class="error">
        {{ session('error') }}
    </div>

@endif

<form method="POST"
      action="{{ route('enrollments.store') }}">

    @csrf

    <table>

        <tr>

            <td width="20%">
                Student
            </td>

            <td>

                <select name="student_id" required>

                    <option value="">
                        Select Student
                    </option>

                    @foreach($students as $student)

                        <option value="{{ $student->id }}">

                            {{ $student->student_name }}

                        </option>

                    @endforeach

                </select>

            </td>

        </tr>

        <tr>

            <td>
                Class
            </td>

            <td>

                <select name="class_id" required>

                    <option value="">
                        Select Class
                    </option>

                    @foreach($classes as $class)

                        <option value="{{ $class->id }}">

                            {{ $class->name }}

                        </option>

                    @endforeach

                </select>

            </td>

        </tr>

        <tr>

            <td>
                Session
            </td>

            <td>

                <select name="session_id" required>

                    <option value="">
                        Select Session
                    </option>

                    @foreach($sessions as $session)

                        <option value="{{ $session->id }}">

                            {{ $session->session_name }}

                        </option>

                    @endforeach

                </select>

            </td>

        </tr>

        <tr>

            <td>
                Roll No
            </td>

            <td>

                <input type="text"
                       name="roll_no">

            </td>

        </tr>

        <tr>

            <td>
                Enrollment Date
            </td>

            <td>

                <input type="date"
                       name="enrollment_date">

            </td>

        </tr>

    </table>

    <br>

    <button type="submit"
            class="btn">

        Save Enrollment

    </button>

</form>

</body>
</html>

@endsection
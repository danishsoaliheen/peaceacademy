@extends('layouts.dashboard')

@section('content')

<div class="container">

    <h2>Bulk Student Promotion</h2>

    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif

    @if(session('error'))

        <div class="alert alert-danger">
            {{ session('error') }}
        </div>

    @endif

    <form method="GET"
          action="{{ route('promotion.preview') }}">

        <div style="display:flex;gap:20px;margin-bottom:20px;">

            <div>
                <label>From Session</label>

                <select name="from_session_id">

                    <option value="">All Sessions</option>

                    @foreach($sessions as $session)

                        <option value="{{ $session->id }}"
                            {{ $fromSession == $session->id ? 'selected' : '' }}>

                            {{ $session->session_name }}

                        </option>

                    @endforeach

                </select>
            </div>

            <div>

                <label>Class</label>

                <select name="class_id">

                    <option value="">All Classes</option>

                    @foreach($classes as $class)

                        <option value="{{ $class->id }}"
                            {{ $classId == $class->id ? 'selected' : '' }}>

                            {{ $class->class_name }}

                        </option>

                    @endforeach

                </select>
            </div>

            <div style="padding-top:24px;">

                <button type="submit">
                    Filter
                </button>

            </div>

        </div>

    </form>

    <form method="POST"
          action="{{ route('promotion.execute') }}">

        @csrf

        <table border="1"
               width="100%"
               cellpadding="10">

            <thead>

                <tr>

                    <th>#</th>
                    <th>Student</th>
                    <th>Current Class</th>
                    <th>Current Session</th>

                </tr>

            </thead>

            <tbody>

                @foreach($students as $row)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            {{ $row->student->student_name ?? '' }}
                        </td>

                        <td>
                            {{ $row->class->class_name ?? '' }}
                        </td>

                        <td>
                            {{ $row->session->session_name ?? '' }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

        <br>

        <div style="width:300px;">

            <label>Promote To Session</label>

            <select name="to_session_id" required>

                <option value="">
                    Select Session
                </option>

                @foreach($sessions as $session)

                    <option value="{{ $session->id }}">

                        {{ $session->session_name }}

                    </option>

                @endforeach

            </select>

        </div>

        <br>

        <button type="submit">
            Promote Students
        </button>

    </form>

</div>

@endsection
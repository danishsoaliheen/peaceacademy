@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">Promote Students</div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('pa_promotions.run') }}">
            @csrf

            <div class="row">

                <div class="col-md-4">
                    <label>From Session</label>
                    <select name="from_session" class="form-control">
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">
                                {{ $session->session_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>To Session</label>
                    <select name="to_session" class="form-control">
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">
                                {{ $session->session_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <br>

            <button class="btn btn-danger">
                Promote All Students
            </button>

        </form>

    </div>
</div>

@endsection
@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="container mt-4">

    <h3>

        Previous Balance Management

    </h3>

    <div class="mb-3">

        <form method="POST"
              action="{{ route('previous-balances.bulk-carry-forward') }}">

            @csrf

            <button class="btn btn-danger">

                Carry Forward All Outstanding Balances

            </button>

        </form>

    </div>

    <table class="table table-bordered table-striped">

        <thead>

        <tr>

            <th>Admission No</th>
            <th>Student</th>
            <th>Class</th>
            <th>Outstanding</th>
            <th>Months Overdue</th>
            <th>Oldest Due Date</th>
            <th>Action</th>

        </tr>

        </thead>

        <tbody>

        @foreach($studentBalances as $row)

            <tr>

                <td>

                    {{ $row['student']->admission_no }}

                </td>

                <td>

                    {{ strtoupper($row['student']->student_name) }}

                </td>

                <td>

                    {{ $row['enrollment']?->class?->class_name }}

                </td>

                <td>

                    <strong class="text-danger">

                        {{ number_format($row['total_balance'],0) }}

                    </strong>

                </td>

                <td>

                    {{ $row['months_overdue'] }}

                </td>

                <td>

                    {{ $row['oldest_due_date'] }}

                </td>

                <td>

                    <form method="POST"
                          action="{{ route('previous-balances.carry-forward') }}">

                        @csrf

                        <input type="hidden"
                               name="student_id"
                               value="{{ $row['student']->id }}">

                        <button class="btn btn-sm btn-primary">

                            Carry Forward

                        </button>

                    </form>

                </td>

            </tr>

        @endforeach

        </tbody>

        <tfoot>

        <tr>

            <th colspan="3">

                Grand Total

            </th>

            <th>

                {{ number_format($grandTotal,0) }}

            </th>

            <th colspan="3"></th>

        </tr>

        </tfoot>

    </table>

</div>

@endsection
@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

    body{
        font-family:Arial, sans-serif;
        background:#f5f5f5;
    }

    .page-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
    }

    .page-title{
        margin:0;
    }

    .table-container{
        background:white;
        padding:20px;
        border-radius:8px;
        box-shadow:0 0 10px rgba(0,0,0,0.05);
        overflow-x:auto;
    }

    table{
        width:100%;
        border-collapse:collapse;
    }

    th,
    td{
        border:1px solid #dcdcdc;
        padding:10px;
        text-align:left;
        vertical-align:middle;
        font-size:14px;
    }

    th{
        background:#f0f0f0;
    }

    .text-end{
        text-align:right;
    }

    .btn{
        display:inline-block;
        padding:6px 12px;
        border-radius:4px;
        text-decoration:none;
        color:white;
        font-size:13px;
        margin:2px;
    }

    .btn-primary{
        background:#0d6efd;
    }

    .btn-success{
        background:#198754;
    }

    .btn-dark{
        background:#212529;
    }

    .btn-danger{
        background:#dc3545;
    }

    .summary-box{
        display:flex;
        gap:15px;
        margin-bottom:20px;
    }

    .summary-card{
        background:white;
        padding:15px 25px;
        border-radius:8px;
        box-shadow:0 0 10px rgba(0,0,0,0.05);
        text-align:center;
        flex:1;
    }

    .summary-card .label{
        font-size:13px;
        color:#666;
        margin-bottom:5px;
    }

    .summary-card .value{
        font-size:20px;
        font-weight:bold;
    }

    .text-green{
        color:#198754;
    }

    .text-muted{
        color:#6c757d;
    }

</style>

<div class="container mt-4">

    <!-- ===================================================== -->
    <!-- Page Header -->
    <!-- ===================================================== -->

    <div class="page-header">

        <h2 class="page-title">
            Payment History
        </h2>

        <a href="{{ route('fee-vouchers.index') }}"
           class="btn btn-primary">

            <i class="fas fa-arrow-left"></i> Back to Vouchers

        </a>

    </div>

    <!-- ===================================================== -->
    <!-- Summary Cards -->
    <!-- ===================================================== -->

    <div class="summary-box">

        <div class="summary-card">
            <div class="label">Total Received</div>
            <div class="value text-green">
                Rs. {{ number_format($totalReceived, 0) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="label">Total Payments</div>
            <div class="value">
                {{ $payments->total() }}
            </div>
        </div>

    </div>

    <!-- ===================================================== -->
    <!-- Filter Form -->
    <!-- ===================================================== -->

    <div class="table-container">

        <form method="GET" class="row g-2 mb-3">

            <div class="col-md-3">
                <select name="student_id" class="form-select">
                    <option value="">All Students</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}"
                            {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ strtoupper($student->student_name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <input type="date"
                       name="from_date"
                       class="form-control"
                       value="{{ request('from_date') }}"
                       placeholder="From Date">
            </div>

            <div class="col-md-2">
                <input type="date"
                       name="to_date"
                       class="form-control"
                       value="{{ request('to_date') }}"
                       placeholder="To Date">
            </div>

            <div class="col-md-2">
                <a href="{{ route('fee-payments.index') }}"
                   class="btn btn-danger w-100"
                   style="display:inline-flex;align-items:center;justify-content:center;padding:8px;">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>

            <div class="col-md-1">
                <button class="btn btn-primary w-100">
                    Go
                </button>
            </div>

        </form>

        <!-- ===================================================== -->
        <!-- Payments Table -->
        <!-- ===================================================== -->

        <table>

            <thead>

                <tr>

                    <th width="5%">ID</th>

                    <th width="10%">Receipt No</th>

                    <th width="18%">Student</th>

                    <th width="10%">Voucher</th>

                    <th width="12%">Amount Paid</th>

                    <th width="10%">Date</th>

                    <th width="10%">Method</th>

                    <th width="10%">Received By</th>

                    <th width="15%">Actions</th>

                </tr>

            </thead>

            <tbody>

                @forelse($payments as $payment)

                    <tr>

                        <td>{{ $payment->id }}</td>

                        <td>
                            <strong>{{ $payment->receipt_no }}</strong>
                        </td>

                        <td>
                            {{ strtoupper($payment->student->student_name ?? '') }}
                        </td>

                        <td>{{ $payment->voucher->voucher_no ?? '' }}</td>

                        <td class="text-end text-green">
                            <strong>{{ number_format($payment->amount_paid, 0) }}</strong>
                        </td>

                        <td>{{ date('d-M-Y', strtotime($payment->payment_date)) }}</td>

                        <td>{{ $payment->payment_method }}</td>

                        <td>{{ $payment->received_by }}</td>

                        <td>

                            <a href="{{ route('fee-payments.receipt', $payment->id) }}"
                               target="_blank"
                               class="btn btn-dark">
                                Receipt
                            </a>

                            <a href="{{ route('fee-payments.edit', $payment->id) }}"
                               class="btn btn-primary">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('fee-payments.destroy', $payment->id) }}"
                                  style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to reverse this payment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    Reverse
                                </button>
                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9" style="text-align:center;">
                            No payments found.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

        <br>

        {{ $payments->links() }}

    </div>

</div>

@endsection
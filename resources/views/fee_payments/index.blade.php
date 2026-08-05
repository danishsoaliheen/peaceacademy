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

    th.sortable{ padding:0; }

    .sort-link{
        display:flex;
        align-items:center;
        gap:5px;
        padding:10px;
        color:#333;
        text-decoration:none;
        white-space:nowrap;
    }
    .sort-link:hover{ color:#0d6efd; }
    .sort-link .sort-icon{ font-size:10px; opacity:.4; }
    .sort-link.active{ color:#0d6efd; font-weight:700; }
    .sort-link.active .sort-icon{ opacity:1; }

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
        border:none;
        cursor:pointer;
    }

    .btn-primary{ background:#0d6efd; }
    .btn-success{ background:#198754; }
    .btn-dark{ background:#212529; }
    .btn-danger{ background:#dc3545; }

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

    .text-green{ color:#198754; }
    .text-muted{ color:#6c757d; }

    .rcpt-link, .vno-link{
        font-family:monospace;
        font-size:13px;
        color:#0d6efd;
        text-decoration:none;
        font-weight:700;
    }
    .rcpt-link:hover, .vno-link:hover{ text-decoration:underline; }

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
            <div class="label">Total Received ({{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }})</div>
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

        <form method="GET" class="row g-2 mb-3" id="filterForm">

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
                       value="{{ $fromDate }}">
            </div>

            <div class="col-md-2">
                <input type="date"
                       name="to_date"
                       class="form-control"
                       value="{{ $toDate }}">
            </div>

            {{-- Carry current sort along with filter submissions --}}
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <div class="col-md-2">
                <a href="{{ route('fee-payments.index') }}"
                   class="btn btn-danger w-100"
                   style="display:inline-flex;align-items:center;justify-content:center;padding:8px;">
                    <i class="fas fa-times"></i> Reset (This Month)
                </a>
            </div>

            <div class="col-md-1">
                <button class="btn btn-primary w-100">
                    Go
                </button>
            </div>

            <div class="col-md-2">
                <a href="{{ route('fee-payments.export', request()->query()) }}"
                   class="btn btn-success w-100"
                   style="display:inline-flex;align-items:center;justify-content:center;padding:8px;">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>

        </form>

        @php
            // Column-specific default direction the first time it's clicked.
            $sortDefaultDirections = [
                'id'             => 'desc',
                'receipt_no'     => 'desc',
                'student'        => 'asc',
                'voucher'        => 'asc',
                'amount_paid'    => 'desc',
                'payment_date'   => 'desc',
                'payment_method' => 'asc',
                'received_by'    => 'asc',
            ];

            $buildSortUrl = function (string $column) use ($sort, $direction, $sortDefaultDirections) {
                $newDirection = $sort === $column
                    ? ($direction === 'asc' ? 'desc' : 'asc')
                    : $sortDefaultDirections[$column];

                $params = array_merge(
                    request()->except('page'),
                    ['sort' => $column, 'direction' => $newDirection]
                );

                return request()->url() . '?' . http_build_query($params);
            };

            $sortIcon = function (string $column) use ($sort, $direction) {
                if ($sort !== $column) return 'fa-sort';
                return $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
            };
        @endphp

        <!-- ===================================================== -->
        <!-- Payments Table -->
        <!-- ===================================================== -->

        <table>

            <thead>

                <tr>

                    <th class="sortable" width="5%">
                        <a href="{{ $buildSortUrl('id') }}" class="sort-link {{ $sort === 'id' ? 'active' : '' }}">
                            ID <i class="fas sort-icon {{ $sortIcon('id') }}"></i>
                        </a>
                    </th>

                    <th class="sortable" width="10%">
                        <a href="{{ $buildSortUrl('receipt_no') }}" class="sort-link {{ $sort === 'receipt_no' ? 'active' : '' }}">
                            Receipt No <i class="fas sort-icon {{ $sortIcon('receipt_no') }}"></i>
                        </a>
                    </th>

                    <th class="sortable" width="16%">
                        <a href="{{ $buildSortUrl('student') }}" class="sort-link {{ $sort === 'student' ? 'active' : '' }}">
                            Student <i class="fas sort-icon {{ $sortIcon('student') }}"></i>
                        </a>
                    </th>

                    <th class="sortable" width="10%">
                        <a href="{{ $buildSortUrl('voucher') }}" class="sort-link {{ $sort === 'voucher' ? 'active' : '' }}">
                            Voucher <i class="fas sort-icon {{ $sortIcon('voucher') }}"></i>
                        </a>
                    </th>

                    <th class="sortable" width="10%">
                        <a href="{{ $buildSortUrl('amount_paid') }}" class="sort-link {{ $sort === 'amount_paid' ? 'active' : '' }}">
                            Amount Paid <i class="fas sort-icon {{ $sortIcon('amount_paid') }}"></i>
                        </a>
                    </th>

                    <th class="sortable" width="10%">
                        <a href="{{ $buildSortUrl('payment_date') }}" class="sort-link {{ $sort === 'payment_date' ? 'active' : '' }}">
                            Date <i class="fas sort-icon {{ $sortIcon('payment_date') }}"></i>
                        </a>
                    </th>

                    <th class="sortable" width="9%">
                        <a href="{{ $buildSortUrl('payment_method') }}" class="sort-link {{ $sort === 'payment_method' ? 'active' : '' }}">
                            Method <i class="fas sort-icon {{ $sortIcon('payment_method') }}"></i>
                        </a>
                    </th>

                    <th class="sortable" width="10%">
                        <a href="{{ $buildSortUrl('received_by') }}" class="sort-link {{ $sort === 'received_by' ? 'active' : '' }}">
                            Received By <i class="fas sort-icon {{ $sortIcon('received_by') }}"></i>
                        </a>
                    </th>

                    <th width="20%">Actions</th>

                </tr>

            </thead>

            <tbody>

                @forelse($payments as $payment)

                    <tr>

                        <td>{{ $payment->id }}</td>

                        <td>
                            <a href="{{ route('fee-payments.receipt', $payment->id) }}"
                               target="_blank"
                               class="rcpt-link"
                               title="View receipt">
                                {{ $payment->receipt_no }}
                            </a>
                        </td>

                        <td>
                            {{ strtoupper($payment->student->student_name ?? '') }}
                        </td>

                        <td>
                            @if($payment->voucher)
                                <a href="{{ route('fee-vouchers.print', $payment->voucher_id) }}"
                                   target="_blank"
                                   class="vno-link"
                                   title="View voucher">
                                    {{ $payment->voucher->voucher_no }}
                                </a>
                            @else
                                —
                            @endif
                        </td>

                        <td class="text-end text-green">
                            <strong>{{ number_format($payment->amount_paid, 0) }}</strong>
                        </td>

                        <td>{{ date('d-M-Y', strtotime($payment->payment_date)) }}</td>

                        <td>{{ $payment->payment_method }}</td>

                        <td>{{ $payment->received_by }}</td>

                        <td>

                            <a href="{{ route('fee-payments.receipt', ['id' => $payment->id, 'download' => 1]) }}"
                               target="_blank"
                               class="btn btn-dark"
                               title="Save receipt as PDF (will later send via WhatsApp)">
                                <i class="fas fa-paper-plane"></i> Send
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
                            No payments found for this period.
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
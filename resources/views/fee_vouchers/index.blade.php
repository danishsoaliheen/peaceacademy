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

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Status Badges
    |--------------------------------------------------------------------------
    */

    .badge{
        padding:6px 10px;
        border-radius:20px;
        font-size:12px;
        font-weight:bold;
        color:white;
        display:inline-block;
    }

    .badge-unpaid{
        background:#dc3545;
    }

    .badge-partial{
        background:#fd7e14;
    }

    .badge-paid{
        background:#198754;
    }

    .badge-advance{
        background:#0dcaf0;
        color:#000;
    }

    /*
    |--------------------------------------------------------------------------
    | Outstanding Row Highlight
    |--------------------------------------------------------------------------
    */

    .row-unpaid{
        background:#fff5f5;
    }

    .row-partial{
        background:#fff9f0;
    }

    .row-paid{
        background:#f3fff6;
    }

    /*
    |--------------------------------------------------------------------------
    | Amount Styling
    |--------------------------------------------------------------------------
    */

    .amount-red{
        color:#dc3545;
        font-weight:bold;
    }

    .amount-green{
        color:#198754;
        font-weight:bold;
    }

    .amount-orange{
        color:#fd7e14;
        font-weight:bold;
    }

</style>

<div class="container mt-4">

    <!-- ===================================================== -->
    <!-- Page Header -->
    <!-- ===================================================== -->

    <div class="page-header">

        <h2 class="page-title">
            Fee Voucher List
        </h2>

        <a href="{{ route('fee-vouchers.create') }}"
           class="btn btn-primary">

            + Create New Voucher

        </a>

    </div>

    <!-- ===================================================== -->
    <!-- Voucher Table -->
    <!-- ===================================================== -->

    <div class="table-container">

<form method="GET" class="row g-2 mb-3">

    <div class="col-md-3">
        <input type="text"
               name="search"
               class="form-control"
               value="{{ request('search') }}"
               placeholder="Voucher / Student">
    </div>

    <div class="col-md-2">
        <select name="status" class="form-select">

            <option value="">All Status</option>

            <option value="paid"
                {{ request('status')=='paid' ? 'selected' : '' }}>
                Paid
            </option>

            <option value="partial"
                {{ request('status')=='partial' ? 'selected' : '' }}>
                Partial
            </option>

            <option value="unpaid"
                {{ request('status')=='unpaid' ? 'selected' : '' }}>
                Unpaid
            </option>

        </select>
    </div>

    <div class="col-md-2">
        <select name="class_id" class="form-select">

            <option value="">All Classes</option>

            @foreach($classes as $class)

                <option value="{{ $class->id }}"
                    {{ request('class_id') == $class->id ? 'selected' : '' }}>

                    {{ $class->class_name }}

                </option>

            @endforeach

        </select>
    </div>

    <div class="col-md-2">
        <select name="month" class="form-select">

            <option value="">All Months</option>

            @for($m=1;$m<=12;$m++)

                <option value="{{ $m }}"
                    {{ request('month') == $m ? 'selected' : '' }}>

                    {{ date('F', mktime(0,0,0,$m,1)) }}

                </option>

            @endfor

        </select>
    </div>

    <div class="col-md-2">
        <select name="sort" class="form-select">

            <option value="latest"
                {{ request('sort')=='latest' ? 'selected' : '' }}>
                Latest First
            </option>

            <option value="oldest"
                {{ request('sort')=='oldest' ? 'selected' : '' }}>
                Oldest First
            </option>

        </select>
    </div>

    <div class="col-md-1">
        <button class="btn btn-primary w-100">
            Go
        </button>
    </div>

</form>





        <table>

            <thead>

                <tr>

                    <th width="5%">
                        ID
                    </th>

                    <th width="12%">
                        Voucher No
                    </th>

                    <th width="18%">
                        Student
                    </th>

                    <th width="10%">
                        Payable
                    </th>

                    <th width="10%">
                        Paid
                    </th>

                    <th width="10%">
                        Balance
                    </th>

                    <th width="10%">
                        Status
                    </th>

                    <th width="10%">
                        Due Date
                    </th>

                    <th width="15%">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($vouchers as $voucher)
@php

    /*
    |--------------------------------------------------------------------------
    | Convert Status To Lowercase
    |--------------------------------------------------------------------------
    */

    $status = strtolower($voucher->status);

    /*
    |--------------------------------------------------------------------------
    | Row Color Based On Status
    |--------------------------------------------------------------------------
    */

    $rowClass = '';

    if($status == 'unpaid'){

        $rowClass = 'row-unpaid';

    }elseif($status == 'partial'){

        $rowClass = 'row-partial';

    }elseif($status == 'paid'){

        $rowClass = 'row-paid';
    }

    /*
    |--------------------------------------------------------------------------
    | Status Badge Color
    |--------------------------------------------------------------------------
    */

    $badgeClass = 'badge-unpaid';

    if($status == 'paid'){

        $badgeClass = 'badge-paid';

    }elseif($status == 'partial'){

        $badgeClass = 'badge-partial';

    }elseif($status == 'advance'){

        $badgeClass = 'badge-advance';
    }

@endphp
                    <tr class="{{ $rowClass }}">

                        <!-- ID -->

                        <td>

                            {{ $voucher->id }}

                        </td>

                        <!-- Voucher No -->

                        <td>

                            {{ $voucher->voucher_no }}

                        </td>

                        <!-- Student -->

                        <td>

                            @if($voucher->student)

                                <a href="{{ route('students.show', $voucher->student->id) }}"
                                   style="color:#0d6efd;text-decoration:none;font-weight:600;">

                                    {{ strtoupper($voucher->student->student_name ?? '') }}

                                </a>

                                @if(!empty($voucher->student->family_code))

                                    <br>

                                    <a href="{{ route('students.index', ['family_code' => $voucher->student->family_code]) }}"
                                       style="font-size:11px;color:#6c757d;text-decoration:none;"
                                       title="View all students in this family">

                                        <i class="fas fa-users"></i> {{ $voucher->student->family_code }}

                                    </a>

                                    @if(isset($familyOutstanding[$voucher->student->family_code]) && $familyOutstanding[$voucher->student->family_code] > 0)

                                        <span style="font-size:11px;color:#dc3545;font-weight:bold;">

                                            &middot; Due {{ number_format($familyOutstanding[$voucher->student->family_code],0) }}

                                        </span>

                                    @endif

                                @endif

                            @else

                                {{-- No linked student --}}
                                N/A

                            @endif

                        </td>

                        <!-- Payable Amount -->

                        <td class="text-end">

                            {{ number_format($voucher->payable_amount,0) }}

                        </td>

                        <!-- Paid Amount -->

                        <td class="text-end amount-green">

                            {{ number_format($voucher->paid_amount,0) }}

                        </td>

                        <!-- Balance -->

                        <td class="text-end">

                            @if($voucher->balance_amount > 0)

                                <span class="amount-red">

                                    {{ number_format($voucher->balance_amount,0) }}

                                </span>

                            @elseif($voucher->balance_amount < 0)

                                <span class="amount-orange">

                                    {{ number_format(abs($voucher->balance_amount),0) }}

                                    Advance

                                </span>

                            @else

                                <span class="amount-green">

                                    0

                                </span>

                            @endif

                        </td>

                        <!-- Status -->

                        <td>

                            <span class="badge {{ $badgeClass }}">

                                {{ $voucher->status }}

                            </span>

                        </td>

                        <!-- Due Date -->

                        <td>

                            {{ strtoupper(\Carbon\Carbon::parse($voucher->due_date)->format('d-M-Y')) }}

                        </td>

                        <!-- Actions -->

<td>

    <!-- Edit Voucher Button (unpaid / partial only) -->
    @if($status == 'unpaid' || $status == 'partial')

        <a href="{{ route('fee-vouchers.edit', $voucher->id) }}"
           class="btn btn-primary">

            Edit

        </a>

    @endif

    <!-- Fix Payment Method (paid vouchers only) -->
    @if($status == 'paid')
        @php $latestPayment = $voucher->payments->sortByDesc('id')->first(); @endphp
        @if($latestPayment)
        <a href="{{ route('fee-payments.edit', $latestPayment->id) }}"
           class="btn"
           style="background:#7c3aed;color:#fff;"
           title="Correct payment method on this paid voucher">
            <i class="fas fa-pen-to-square"></i> Fix Method
        </a>
        @endif
    @endif

    <!-- Receive Payment Button -->

    <a href="{{ route('fee-payments.create', $voucher->id) }}"
       class="btn btn-success">

        Receive

    </a>


    <!-- Print Button -->

    <a href="{{ route('fee-vouchers.print', $voucher->id) }}"
       target="_blank"
       class="btn btn-dark">

        Print

    </a>

    <!-- Delete Button (only when no payment has ever been recorded) -->

    @if($voucher->payments->count() === 0)

        <form method="POST"
              action="{{ route('fee-vouchers.destroy', $voucher->id) }}"
              style="display:inline;"
              onsubmit="return confirm('Delete voucher {{ $voucher->voucher_no }}? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="btn"
                    style="background:#dc3545;">
                Delete
            </button>
        </form>

    @endif

</td>
                    </tr>

                @empty

                    <tr>

                        <td colspan="9"
                            style="text-align:center;">

                            No vouchers found.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

        <br>

        <!-- ===================================================== -->
        <!-- Pagination -->
        <!-- ===================================================== -->

        {{ $vouchers->links() }}

    </div>

</div>

@endsection
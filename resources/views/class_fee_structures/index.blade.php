@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

    .ledger-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .ledger-card .card-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        padding: 16px 22px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ledger-card .card-header h3 { margin: 0; font-size: 17px; font-weight: 600; }

    .card-body { padding: 20px 22px; }

    /* ── Search ── */
    .search-wrap {
        position: relative;
        margin-bottom: 20px;
    }

    .search-wrap input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border-color .2s;
        box-sizing: border-box;
    }

    .search-wrap input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,.12);
    }

    .search-wrap .search-icon {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }

    /* ── Autocomplete dropdown ── */
    #autocomplete-list {
        display: none;
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        z-index: 999;
        max-height: 240px;
        overflow-y: auto;
    }

    #autocomplete-list .ac-item {
        padding: 9px 14px;
        font-size: 13px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }

    #autocomplete-list .ac-item:last-child { border-bottom: none; }

    #autocomplete-list .ac-item:hover,
    #autocomplete-list .ac-item.active {
        background: #eff6ff;
        color: #1d4ed8;
    }

    #autocomplete-list .ac-item mark {
        background: #dbeafe;
        color: #1d4ed8;
        border-radius: 2px;
        padding: 0 1px;
        font-weight: 700;
    }

    /* ── Table ── */
    .results-table { width: 100%; border-collapse: collapse; }

    .results-table thead tr {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }

    .results-table th {
        padding: 9px 13px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
        font-weight: 600;
    }

    .results-table td {
        padding: 11px 13px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }

    .results-table tr:hover td { background: #f8fafc; }

    .badge-balance {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-balance.has  { background: #fee2e2; color: #dc2626; }
    .badge-balance.none { background: #dcfce7; color: #16a34a; }

    .btn-ledger {
        display: inline-block;
        padding: 5px 12px;
        background: #0d6efd;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }

    .btn-ledger:hover { opacity: .88; color: white; }

    .btn-voucher {
        display: inline-block;
        padding: 5px 12px;
        background: #16a34a;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }

    .btn-voucher:hover { opacity: .88; color: white; }

    /* ── Pagination ── */
    .pagination-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 16px;
        font-size: 13px;
        color: #64748b;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pagination-wrap .links a,
    .pagination-wrap .links span {
        display: inline-block;
        padding: 5px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        margin: 0 2px;
        font-size: 12px;
        text-decoration: none;
        color: #475569;
    }

    .pagination-wrap .links span[aria-current] {
        background: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

/* ===== Class Fee Structure Table ===== */

.fee-table{
    width:100% !important;
    table-layout:auto;
    background:#fff;
}

.fee-table th{
    white-space:nowrap;
    vertical-align:middle;
    padding:12px;
    font-size:13px;
    background:#f8fafc;
}

.fee-table td{
    padding:12px;
    vertical-align:middle;
}

/* Column widths */
.fee-table th:nth-child(1),
.fee-table td:nth-child(1){
    width:15%;
}

.fee-table th:nth-child(2),
.fee-table td:nth-child(2){
    width:18%;
}

.fee-table th:nth-child(3),
.fee-table td:nth-child(3){
    width:12%;
}

.fee-table th:nth-child(4),
.fee-table td:nth-child(4){
    width:8%;
    text-align:center;
}

.fee-table th:nth-child(5),
.fee-table td:nth-child(5){
    width:8%;
    text-align:center;
}

.fee-table th:nth-child(6),
.fee-table td:nth-child(6){
    width:8%;
    text-align:center;
}

.fee-table th:nth-child(7),
.fee-table td:nth-child(7){
    width:21%;
}

.fee-table th:nth-child(8),
.fee-table td:nth-child(8){
    width:10%;
    text-align:center;
    white-space:nowrap;
}

.table-responsive{
    width:100%;
    overflow-x:auto;
}

.container-fluid-custom{
    width:100%;
    max-width:100%;
}

</style>

<div class="container-fluid-custom">
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">

    <h2>Class Fee Structures</h2>

    <div style="display:flex; gap:10px;">

        <a href="{{ route('class-fee-structures.create') }}"
           class="btn">
            + Add New
        </a>

        <a href="{{ route('class-fee-structures.bulk.create') }}"
           class="btn"
           style="background:#6366f1;">
            Bulk Entry
        </a>

        <a href="{{ route('class-fee-structures.import.form') }}"
           class="btn"
           style="background:#0891b2;">
            Import CSV / Excel
        </a>

    </div>

</div>

@if(session('import_errors') && count(session('import_errors')))

    <div style="background:#fef2f2; color:#991b1b; padding:12px; border-radius:5px; margin-bottom:15px;">

        <strong>Some rows were skipped:</strong>

        <ul style="margin-top:8px; padding-left:20px;">

            @foreach(session('import_errors') as $err)

                <li>{{ $err }}</li>

            @endforeach

        </ul>

    </div>

@endif

<div class="table-responsive">
<table class="table table-bordered table-hover fee-table">

<form method="GET"
      style="
      margin-bottom:20px;
      padding:15px;
      background:white;
      border-radius:8px;
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      ">

    <input type="text"
           name="search"
           value="{{ request('search') }}"
           placeholder="Search class, fee type or amount"
           style="padding:8px; min-width:250px;">

    <select name="class_id">

        <option value="">
            All Classes
        </option>

        @foreach($classes as $class)

            <option value="{{ $class->id }}"
                {{ request('class_id') == $class->id ? 'selected' : '' }}>

                {{ $class->class_name }}

            </option>

        @endforeach

    </select>

    <select name="fee_type_id">

        <option value="">
            All Fee Types
        </option>

        @foreach($feeTypes as $fee)

            <option value="{{ $fee->id }}"
                {{ request('fee_type_id') == $fee->id ? 'selected' : '' }}>

                {{ $fee->name }}

            </option>

        @endforeach

    </select>

    <select name="status">

        <option value="">
            All Status
        </option>

        <option value="1"
            {{ request('status') == '1' ? 'selected' : '' }}>
            Active
        </option>

        <option value="0"
            {{ request('status') == '0' ? 'selected' : '' }}>
            Inactive
        </option>

    </select>

    <button type="submit"
            class="btn">
        Search
    </button>

    <a href="{{ route('class-fee-structures.index') }}"
       class="btn"
       style="background:#6b7280;">
        Reset
    </a>

</form>

    <thead>

        <tr>
            <th>Class</th>
            <th>Fee Type</th>
            <th>Amount</th>
            <th>Mandatory</th>
            <th>Discount</th>
            <th>Active</th>
            <th>Notes</th>
            <th style="width:120px;">Actions</th>
        </tr>

    </thead>

    <tbody>

        @forelse($structures as $structure)

            <tr>

                <td>{{ $structure->class->class_name }}</td>

                <td>{{ $structure->feeType->name }}</td>

                <td style="text-align:right;">
                    {{ number_format($structure->amount, 2) }}
                </td>

                <td style="text-align:center;">
                    @if($structure->is_mandatory)
                        <span style="color:#16a34a; font-weight:bold;">Yes</span>
                    @else
                        <span style="color:#6b7280;">No</span>
                    @endif
                </td>

                <td style="text-align:center;">
                    @if($structure->allow_discount)
                        <span style="color:#16a34a; font-weight:bold;">Yes</span>
                    @else
                        <span style="color:#6b7280;">No</span>
                    @endif
                </td>

                <td style="text-align:center;">
                    @if($structure->is_active)
                        <span style="color:#16a34a; font-weight:bold;">Active</span>
                    @else
                        <span style="color:#dc2626;">Inactive</span>
                    @endif
                </td>

                <td style="color:#6b7280; font-size:13px;">
                    {{ $structure->notes ?? '—' }}
                </td>

                <td style="text-align:center; white-space:nowrap;">

                    {{-- Edit --}}
                    <a href="{{ route('class-fee-structures.edit', $structure->id) }}"
                       style="display:inline-block; padding:5px 10px; background:#f59e0b;
                              color:white; border-radius:4px; text-decoration:none;
                              font-size:13px; margin-right:4px;">
                        Edit
                    </a>

                    {{-- Delete --}}
                    <form method="POST"
                          action="{{ route('class-fee-structures.destroy', $structure->id) }}"
                          style="display:inline;"
                          onsubmit="return confirm('Delete this fee structure?')">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                style="padding:5px 10px; background:#dc2626; color:white;
                                       border:none; border-radius:4px; cursor:pointer;
                                       font-size:13px;">
                            Delete
                        </button>

                    </form>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="8"
                    style="text-align:center; color:#6b7280; padding:30px;">
                    No fee structures found. Add one to get started.
                </td>
            </tr>

        @endforelse

    </tbody>
</table>
</div>

</div>

@endsection
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

</style>

<h2>Create Class Fee Structure</h2>

@if($errors->any())

    <div style="color:red;">

        <ul>

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<form method="POST"
      action="{{ route('class-fee-structures.store') }}">

    @csrf

    <p>

        <label>Class</label>

        <select name="class_id" required>

            <option value="">
                Select Class
            </option>

            @foreach($classes as $class)

                <option value="{{ $class->id }}">

                    {{ $class->class_name }}

                </option>

            @endforeach

        </select>

    </p>

    <p>

        <label>Fee Type</label>

        <select name="fee_type_id" required>

            <option value="">
                Select Fee Type
            </option>

            @foreach($feeTypes as $fee)

                <option value="{{ $fee->id }}">

                    {{ $fee->name }}

                </option>

            @endforeach

        </select>

    </p>

    <p>

        <label>Amount</label>

        <input type="number"
               step="0.01"
               name="amount"
               required>

    </p>

    <p>

        <label>

            <input type="checkbox"
                   name="is_mandatory"
                   checked>

            Mandatory

        </label>

    </p>

    <p>

        <label>

            <input type="checkbox"
                   name="allow_discount"
                   checked>

            Allow Discount

        </label>

    </p>

    <p>

        <label>Notes</label>

        <textarea name="notes"></textarea>

    </p>

    <button type="submit">

        Save Structure

    </button>

</form>

@endsection
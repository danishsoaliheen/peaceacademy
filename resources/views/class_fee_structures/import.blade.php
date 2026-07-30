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

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">

    <h2>Import Fee Structures</h2>

    <a href="{{ route('class-fee-structures.index') }}"
       class="btn"
       style="background:#6b7280;">
        &larr; Back to List
    </a>

</div>

@if($errors->any())

    <div style="background:#fef2f2; color:#991b1b; padding:12px;
                border-radius:5px; margin-bottom:20px;">

        <ul style="padding-left:20px;">

            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach

        </ul>

    </div>

@endif

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">

    {{-- Upload Form --}}
    <div style="background:white; padding:25px; border-radius:8px;
                box-shadow:0 2px 5px rgba(0,0,0,0.08);">

        <h3 style="margin-bottom:16px; font-size:16px;">Upload File</h3>

        <form method="POST"
              action="{{ route('class-fee-structures.import.store') }}"
              enctype="multipart/form-data">

            @csrf

            <div style="margin-bottom:16px;">

                <label style="display:block; font-weight:bold; margin-bottom:5px;">
                    Select CSV or Excel File <span style="color:red;">*</span>
                </label>

                <input type="file"
                       name="file"
                       accept=".csv,.xlsx,.xls"
                       required
                       style="width:100%; padding:8px; border:1px solid #ddd;
                              border-radius:4px; background:#f9fafb;">

                <p style="color:#6b7280; font-size:12px; margin-top:5px;">
                    Accepted formats: .csv, .xlsx, .xls
                </p>

            </div>

            <div style="background:#eff6ff; border:1px solid #bfdbfe;
                        padding:12px; border-radius:5px; margin-bottom:20px;">

                <p style="font-size:13px; color:#1e40af; font-weight:bold; margin-bottom:5px;">
                    ℹ️ Import behaviour
                </p>

                <ul style="font-size:13px; color:#1e40af; padding-left:18px; line-height:1.8;">
                    <li>If a record with the same Class + Fee Type already exists, it will be <strong>updated</strong>.</li>
                    <li>New records will be <strong>inserted</strong>.</li>
                    <li>Rows with unrecognised class names or fee types will be <strong>skipped</strong>.</li>
                    <li>Column names in the file must match the sample exactly.</li>
                </ul>

            </div>

            <button type="submit" class="btn" style="width:100%;">
                Upload &amp; Import
            </button>

        </form>

    </div>

    {{-- Format Guide --}}
    <div style="background:white; padding:25px; border-radius:8px;
                box-shadow:0 2px 5px rgba(0,0,0,0.08);">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">

            <h3 style="font-size:16px;">Required File Format</h3>

            <a href="{{ route('class-fee-structures.sample.csv') }}"
               class="btn"
               style="background:#16a34a; font-size:13px; padding:7px 12px;">
                ⬇ Download Sample CSV
            </a>

        </div>

        <p style="font-size:13px; color:#6b7280; margin-bottom:12px;">
            Your file must have these exact column headers in the first row:
        </p>

        <table style="font-size:13px; width:100%;">

            <thead>
                <tr>
                    <th style="text-align:left; padding:8px; background:#f1f5f9;">Column</th>
                    <th style="text-align:left; padding:8px; background:#f1f5f9;">Required</th>
                    <th style="text-align:left; padding:8px; background:#f1f5f9;">Description</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td style="padding:8px; font-family:monospace; color:#0d6efd;">class_name</td>
                    <td style="padding:8px; color:#dc2626;">Yes</td>
                    <td style="padding:8px;">Exact class name as in the system (e.g. "Class 1")</td>
                </tr>

                <tr style="background:#f9fafb;">
                    <td style="padding:8px; font-family:monospace; color:#0d6efd;">fee_type</td>
                    <td style="padding:8px; color:#dc2626;">Yes</td>
                    <td style="padding:8px;">Exact fee type name (e.g. "Monthly Fee")</td>
                </tr>

                <tr>
                    <td style="padding:8px; font-family:monospace; color:#0d6efd;">amount</td>
                    <td style="padding:8px; color:#dc2626;">Yes</td>
                    <td style="padding:8px;">Numeric value, e.g. 1500 or 1500.00</td>
                </tr>

                <tr style="background:#f9fafb;">
                    <td style="padding:8px; font-family:monospace; color:#0d6efd;">is_mandatory</td>
                    <td style="padding:8px; color:#6b7280;">No</td>
                    <td style="padding:8px;">1 / yes / true &nbsp;or&nbsp; 0 / no / false (default: 1)</td>
                </tr>

                <tr>
                    <td style="padding:8px; font-family:monospace; color:#0d6efd;">allow_discount</td>
                    <td style="padding:8px; color:#6b7280;">No</td>
                    <td style="padding:8px;">1 / yes / true &nbsp;or&nbsp; 0 / no / false (default: 0)</td>
                </tr>

                <tr style="background:#f9fafb;">
                    <td style="padding:8px; font-family:monospace; color:#0d6efd;">notes</td>
                    <td style="padding:8px; color:#6b7280;">No</td>
                    <td style="padding:8px;">Any text remark, can be left blank</td>
                </tr>

            </tbody>

        </table>

        <div style="margin-top:16px; padding:10px; background:#fefce8;
                    border-radius:5px; font-size:13px; color:#854d0e;">
            <strong>Tip:</strong> Class names and fee type names are matched
            case-insensitively, so "class 1" and "Class 1" both work.
        </div>

    </div>

</div>

@endsection

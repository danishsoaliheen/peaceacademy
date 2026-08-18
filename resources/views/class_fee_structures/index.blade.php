@extends('layouts.dashboard')

@section('content')

@php
    // Column-specific default direction the first time it's clicked
    // (matches ClassFeeStructureController::SORT_DEFAULT_DIRECTIONS).
    $sortDefaultDirections = [
        'class'     => 'asc',
        'fee_type'  => 'asc',
        'amount'    => 'desc',
        'mandatory' => 'desc',
        'discount'  => 'desc',
        'status'    => 'desc',
    ];

    $buildSortUrl = function (string $column) use ($sort, $direction, $sortDefaultDirections) {
        $newDirection = $sort === $column
            ? ($direction === 'asc' ? 'desc' : 'asc')
            : $sortDefaultDirections[$column];

        // Keep every current filter (search, class_id, status…) but drop
        // the old page number so sorting starts back at page 1.
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

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-layer-group me-2" style="opacity:.8;"></i>Class Fee Structures</h2>
            <p>Define how much each class pays per fee type — used by vouchers and the monthly fee engine</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('class-fee-structures.create') }}" class="btn-hero-ghost">
                <i class="fas fa-plus"></i> Add New
            </a>
            <a href="{{ route('class-fee-structures.bulk.create') }}" class="btn-hero-ghost">
                <i class="fas fa-list-ol"></i> Bulk Entry
            </a>
            <a href="{{ route('class-fee-structures.import.form') }}" class="btn-hero-ghost">
                <i class="fas fa-file-import"></i> Import CSV / Excel
            </a>
        </div>
    </div>
</div>

{{-- ── Flash / import errors ────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('import_errors') && count(session('import_errors')))
    <div class="alert alert-warning alert-dismissible fade show">
        <strong><i class="fas fa-exclamation-triangle me-1"></i>Some rows were skipped:</strong>
        <ul class="mb-0 mt-2">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<div class="filter-card card">
    <div class="card-body">
        <form method="GET" action="{{ route('class-fee-structures.index') }}" id="filterForm">
            <div class="row g-2">

                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:8px 0 0 8px;">
                            <i class="fas fa-search text-muted" style="font-size:.8rem;"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control border-start-0 ps-0"
                               style="border-radius:0 8px 8px 0;"
                               placeholder="Search class, fee type or amount…">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="class_id" class="form-select filter-auto">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="fee_type_id" class="form-select filter-auto">
                        <option value="">All Fee Types</option>
                        @foreach($feeTypes as $fee)
                            <option value="{{ $fee->id }}" {{ request('fee_type_id') == $fee->id ? 'selected' : '' }}>
                                {{ $fee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select filter-auto">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                {{-- Sorting is done via the clickable column headers below;
                     these hidden fields just carry the current sort along
                     when a filter is applied so it isn't reset to default. --}}
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark flex-grow-1" style="border-radius:8px; font-size:.85rem;">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search','class_id','fee_type_id','status']))
                        <a href="{{ route('class-fee-structures.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;" title="Clear filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Fee structure table ──────────────────────────────────────────────── --}}
<div class="section-card card">

    <div class="card-header" style="justify-content:space-between;">
        <div class="d-flex align-items-center gap-2">
            <span class="s-icon" style="background:#1e293b;"><i class="fas fa-list"></i></span>
            <h6>Fee Structure Records</h6>
        </div>
        <small class="text-muted">
            Showing {{ $structures->firstItem() ?? 0 }}–{{ $structures->lastItem() ?? 0 }} of {{ $structures->total() }}
        </small>
    </div>

    <div class="table-responsive">
        <table class="table pa-table mb-0">
            <thead>
                <tr>
                    <th class="sortable">
                        <a href="{{ $buildSortUrl('class') }}" class="sort-link text-white text-decoration-none {{ $sort === 'class' ? 'active' : '' }}">
                            Class
                            <i class="fas {{ $sortIcon('class') }} sort-icon"></i>
                        </a>
                    </th>
                    <th class="sortable">
                        <a href="{{ $buildSortUrl('fee_type') }}" class="sort-link text-white text-decoration-none {{ $sort === 'fee_type' ? 'active' : '' }}">
                            Fee Type
                            <i class="fas {{ $sortIcon('fee_type') }} sort-icon"></i>
                        </a>
                    </th>
                    <th class="sortable text-end">
                        <a href="{{ $buildSortUrl('amount') }}" class="sort-link text-white text-decoration-none justify-content-end {{ $sort === 'amount' ? 'active' : '' }}">
                            Amount
                            <i class="fas {{ $sortIcon('amount') }} sort-icon"></i>
                        </a>
                    </th>
                    <th class="sortable text-center">
                        <a href="{{ $buildSortUrl('mandatory') }}" class="sort-link text-white text-decoration-none justify-content-center {{ $sort === 'mandatory' ? 'active' : '' }}">
                            Mandatory
                            <i class="fas {{ $sortIcon('mandatory') }} sort-icon"></i>
                        </a>
                    </th>
                    <th class="sortable text-center">
                        <a href="{{ $buildSortUrl('discount') }}" class="sort-link text-white text-decoration-none justify-content-center {{ $sort === 'discount' ? 'active' : '' }}">
                            Discount
                            <i class="fas {{ $sortIcon('discount') }} sort-icon"></i>
                        </a>
                    </th>
                    <th class="sortable text-center">
                        <a href="{{ $buildSortUrl('status') }}" class="sort-link text-white text-decoration-none justify-content-center {{ $sort === 'status' ? 'active' : '' }}">
                            Active
                            <i class="fas {{ $sortIcon('status') }} sort-icon"></i>
                        </a>
                    </th>
                    <th>Notes</th>
                    <th width="110" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($structures as $structure)
                <tr>
                    <td style="font-weight:700; color:#1e293b;">{{ $structure->class->class_name }}</td>

                    <td>{{ $structure->feeType->name }}</td>

                    <td class="text-end" style="font-weight:700;">
                        {{ number_format($structure->amount, 0) }}
                    </td>

                    <td class="text-center">
                        @if($structure->is_mandatory)
                            <span class="pa-badge pa-badge-green">Yes</span>
                        @else
                            <span class="pa-badge pa-badge-gray">No</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($structure->allow_discount)
                            <span class="pa-badge pa-badge-green">Yes</span>
                        @else
                            <span class="pa-badge pa-badge-gray">No</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($structure->is_active)
                            <span class="pa-badge pa-badge-green">Active</span>
                        @else
                            <span class="pa-badge pa-badge-red">Inactive</span>
                        @endif
                    </td>

                    <td class="text-muted" style="font-size:.82rem;">
                        {{ $structure->notes ?? '—' }}
                    </td>

                    <td>
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('class-fee-structures.edit', $structure->id) }}"
                               class="btn-icon btn-icon-yellow" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form method="POST"
                                  action="{{ route('class-fee-structures.destroy', $structure->id) }}"
                                  onsubmit="return confirm('Delete this fee structure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon btn-icon-red" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-inbox fa-2x text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-2">No fee structures found. Add one to get started.</p>
                        @if(request()->anyFilled(['search','class_id','fee_type_id','status']))
                            <a href="{{ route('class-fee-structures.index') }}" class="btn btn-sm btn-outline-secondary">
                                Clear filters
                            </a>
                        @else
                            <a href="{{ route('class-fee-structures.create') }}" class="btn-icon btn-icon-blue" style="display:inline-flex;">
                                <i class="fas fa-plus"></i> Add First Structure
                            </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ── Pagination ───────────────────────────────────────────────────────── --}}
@if($structures->hasPages())
    <div class="pa-pagination-wrap">
        {{ $structures->links() }}
    </div>
@endif

<style>
    /* Sortable column headers — mirrors the pattern used on the Student
       Ledger and Payment History list pages. */
    .pa-table th.sortable { padding: 0 !important; }

    .sort-link {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 11px 16px;
        white-space: nowrap;
    }
    .sort-link .sort-icon { font-size: 10px; opacity: .45; }
    .sort-link.active .sort-icon { opacity: 1; }
</style>

@push('scripts')
<script>
    // Auto-submit the filter form when a dropdown changes.
    document.querySelectorAll('.filter-auto').forEach(function (el) {
        el.addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@endpush

@endsection
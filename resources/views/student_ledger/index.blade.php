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

    .badge-overdue {
        display: inline-block;
        margin-left: 5px;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        background: #7f1d1d;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    /* ── Summary stat cards ── */
    .stats-row {
        display: flex;
        gap: 14px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .stat-card {
        flex: 1;
        min-width: 150px;
        background: #fff;
        border-radius: 8px;
        padding: 14px 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-top: 3px solid #e2e8f0;
    }
    .stat-card.total     { border-top-color: #3b82f6; }
    .stat-card.dues      { border-top-color: #f97316; }
    .stat-card.overdue   { border-top-color: #dc2626; }
    .stat-card.amount    { border-top-color: #16a34a; }
    .stat-card label { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
    .stat-card .val { font-size: 20px; font-weight: 700; color: #1e293b; }

    /* ── Filter row ── */
    .filter-row {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        align-items: center;
    }
    .filter-row select,
    .filter-row .form-control {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
    }
    .filter-check {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #475569;
    }
    .btn-go, .btn-clear, .btn-export {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    .btn-go     { background: #0d6efd; color: #fff; }
    .btn-clear  { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    .btn-export { background: #16a34a; color: #fff; margin-left: auto; }
    .btn-export:hover { opacity: .88; color: #fff; }
    .last-voucher-link {
        font-family: monospace;
        font-size: 11px;
        color: #7c3aed;
        text-decoration: none;
    }
    .last-voucher-link:hover { text-decoration: underline; }

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

    /* ── Sortable column headers ── */
    .results-table th.sortable { padding: 0; }

    .sort-link {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 9px 13px;
        color: #64748b;
        text-decoration: none;
        white-space: nowrap;
    }

    .sort-link:hover { color: #0d6efd; }

    .sort-link .sort-icon {
        font-size: 10px;
        opacity: .35;
    }

    .sort-link.active {
        color: #1e293b;
        font-weight: 700;
    }

    .sort-link.active .sort-icon {
        opacity: 1;
        color: #0d6efd;
    }

</style>

<div class="ledger-card">

    <div class="card-header">
        <i class="fas fa-book-open" style="font-size:18px;"></i>
        <h3>Student Ledger</h3>
    </div>

    <div class="card-body">

        {{-- Summary Stat Cards --}}
        <div class="stats-row">
            <div class="stat-card total">
                <label>Students (Filtered)</label>
                <div class="val">{{ $totalStudentsMatched }}</div>
            </div>
            <div class="stat-card dues">
                <label>With Dues</label>
                <div class="val">{{ $studentsWithDues }}</div>
            </div>
            <div class="stat-card overdue">
                <label>Overdue</label>
                <div class="val">{{ $studentsOverdue }}</div>
            </div>
            <div class="stat-card amount">
                <label>Total Outstanding</label>
                <div class="val">Rs. {{ number_format($totalOutstandingAll, 0) }}</div>
            </div>
        </div>

        {{-- Search box with autocomplete + filters (single form so nothing gets lost on submit) --}}
        <form method="GET" action="{{ route('student-ledger.index') }}" id="searchForm">

            <div class="search-wrap">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       id="searchInput"
                       name="search"
                       placeholder="Type student name to search…"
                       value="{{ $query }}"
                       autocomplete="off">
                <div id="autocomplete-list"></div>
            </div>

            <div class="filter-row">

                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ (string)$classFilter === (string)$class->id ? 'selected' : '' }}>
                            {{ $class->class_name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-select">
                    <option value="1" {{ (string)$statusFilter === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ (string)$statusFilter === '0' ? 'selected' : '' }}>Inactive</option>
                    <option value="all" {{ (string)$statusFilter === 'all' ? 'selected' : '' }}>All</option>
                </select>

                {{-- Sorting is done via the clickable column headers below;
                     these hidden fields just carry the current sort along
                     when a filter is applied so it isn't reset to default. --}}
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">

                <label class="filter-check">
                    <input type="checkbox" name="outstanding_only" value="1" {{ $outstandingOnly ? 'checked' : '' }}>
                    Outstanding Only
                </label>

                <button type="submit" class="btn-go">
                    <i class="fas fa-filter"></i> Apply
                </button>

                <a href="{{ route('student-ledger.index') }}" class="btn-clear">
                    <i class="fas fa-times"></i> Reset
                </a>

                <a href="{{ route('student-ledger.export', request()->query()) }}"
                   class="btn-export"
                   target="_blank">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>

            </div>

        </form>

        {{-- Results info --}}
        <div style="margin-bottom:12px; font-size:13px; color:#64748b;">
            Showing <strong>{{ $students->firstItem() }}–{{ $students->lastItem() }}</strong>
            of <strong>{{ $students->total() }}</strong> students
            @if($query) — filtered by "<strong>{{ $query }}</strong>" @endif
        </div>

        @php
            // Column-specific default direction the first time it's clicked
            // (matches StudentLedgerController::SORT_DEFAULT_DIRECTIONS).
            $sortDefaultDirections = [
                'name'         => 'asc',
                'admission_no' => 'asc',
                'father_name'  => 'asc',
                'class'        => 'asc',
                'outstanding'  => 'desc',
            ];

            $buildSortUrl = function (string $column) use ($sort, $direction, $sortDefaultDirections) {
                $newDirection = $sort === $column
                    ? ($direction === 'asc' ? 'desc' : 'asc')
                    : $sortDefaultDirections[$column];

                // Keep every current filter (search, class_id, outstanding_only…)
                // but drop the old page number so sorting starts back at page 1.
                $params = array_merge(
                    request()->except('page'),
                    ['sort' => $column, 'direction' => $newDirection]
                );

                return request()->url() . '?' . http_build_query($params);
            };
        @endphp

        {{-- Table --}}
        <table class="results-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th class="sortable">
                        <a href="{{ $buildSortUrl('admission_no') }}"
                           class="sort-link {{ $sort === 'admission_no' ? 'active' : '' }}">
                            Adm. No
                            <i class="fas sort-icon fa-sort{{ $sort === 'admission_no' ? ($direction === 'asc' ? '-up' : '-down') : '' }}"></i>
                        </a>
                    </th>
                    <th class="sortable">
                        <a href="{{ $buildSortUrl('name') }}"
                           class="sort-link {{ $sort === 'name' ? 'active' : '' }}">
                            Student Name
                            <i class="fas sort-icon fa-sort{{ $sort === 'name' ? ($direction === 'asc' ? '-up' : '-down') : '' }}"></i>
                        </a>
                    </th>
                    <th class="sortable">
                        <a href="{{ $buildSortUrl('father_name') }}"
                           class="sort-link {{ $sort === 'father_name' ? 'active' : '' }}">
                            Father Name
                            <i class="fas sort-icon fa-sort{{ $sort === 'father_name' ? ($direction === 'asc' ? '-up' : '-down') : '' }}"></i>
                        </a>
                    </th>
                    <th class="sortable">
                        <a href="{{ $buildSortUrl('class') }}"
                           class="sort-link {{ $sort === 'class' ? 'active' : '' }}">
                            Class
                            <i class="fas sort-icon fa-sort{{ $sort === 'class' ? ($direction === 'asc' ? '-up' : '-down') : '' }}"></i>
                        </a>
                    </th>
                    <th>Last Voucher</th>
                    <th class="sortable">
                        <a href="{{ $buildSortUrl('outstanding') }}"
                           class="sort-link {{ $sort === 'outstanding' ? 'active' : '' }}">
                            Outstanding
                            <i class="fas sort-icon fa-sort{{ $sort === 'outstanding' ? ($direction === 'asc' ? '-up' : '-down') : '' }}"></i>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            @forelse($students as $student)
                <tr>
                    <td style="color:#94a3b8;">{{ $students->firstItem() + $loop->index }}</td>

                    <td>
                        <span style="font-family:monospace; font-size:12px; background:#f1f5f9;
                                     padding:2px 7px; border-radius:3px; color:#475569;">
                            {{ $student->admission_no }}
                        </span>
                    </td>

                    <td><strong>{{ strtoupper($student->student_name) }}</strong></td>

                    <td style="color:#64748b;">{{ strtoupper($student->father_name ?? '—') }}</td>

                    <td>
                        @if($student->activeEnrollment?->class?->class_name)
                            <span style="background:#e0f2fe; color:#0369a1; padding:2px 9px;
                                         border-radius:12px; font-size:12px;">
                                {{ $student->activeEnrollment->class->class_name }}
                            </span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>

                    <td>
                        @if($student->last_voucher)
                            <a href="{{ route('fee-vouchers.print', $student->last_voucher->id) }}"
                               target="_blank"
                               class="last-voucher-link"
                               title="Open latest voucher">
                                {{ $student->last_voucher->voucher_no }}
                                <i class="fas fa-external-link-alt" style="font-size:9px;"></i>
                            </a>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>

                    <td>
                        @if($student->outstanding_balance > 0)
                            <span class="badge-balance has">
                                Rs. {{ number_format($student->outstanding_balance, 0) }}
                            </span>
                            @if($student->overdue_count > 0)
                                <span class="badge-overdue">Overdue</span>
                            @endif
                        @else
                            <span class="badge-balance none">Clear</span>
                        @endif
                    </td>

                    <td style="display:flex; gap:5px;">
                        <a href="{{ route('student-ledger.show', $student->id) }}"
                           class="btn-ledger">
                            <i class="fas fa-book"></i> Ledger
                        </a>
                        <a href="{{ route('fee-vouchers.create', ['student_id' => $student->id]) }}"
                           class="btn-voucher">
                            <i class="fas fa-plus"></i> Voucher
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8;">
                        No students found{{ $query ? ' matching "' . $query . '"' : '' }}.
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>

        {{-- Pagination --}}
        @if($students->hasPages())
        <div class="pagination-wrap">
            <div>Page {{ $students->currentPage() }} of {{ $students->lastPage() }}</div>
            <div class="links">{{ $students->links() }}</div>
        </div>
        @endif

    </div>

</div>

{{-- Autocomplete names passed from controller --}}
<script>
    const ALL_NAMES = @json($allNames);

    const input   = document.getElementById('searchInput');
    const list    = document.getElementById('autocomplete-list');
    const form    = document.getElementById('searchForm');
    let activeIdx = -1;

    input.addEventListener('input', function () {

        const val = this.value.trim().toLowerCase();
        list.innerHTML = '';
        activeIdx = -1;

        if (!val) { list.style.display = 'none'; return; }

        const matches = ALL_NAMES
            .filter(n => n.toLowerCase().includes(val))
            .slice(0, 10);

        if (!matches.length) { list.style.display = 'none'; return; }

        matches.forEach(function (name) {

            const div = document.createElement('div');
            div.className = 'ac-item';

            // Highlight matching part
            const re  = new RegExp('(' + val.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            div.innerHTML = name.replace(re, '<mark>$1</mark>');

            div.addEventListener('mousedown', function (e) {
                e.preventDefault();
                input.value = name;
                list.style.display = 'none';
                form.submit();
            });

            list.appendChild(div);
        });

        list.style.display = 'block';
    });

    // Keyboard navigation
    input.addEventListener('keydown', function (e) {

        const items = list.querySelectorAll('.ac-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, -1);
        } else if (e.key === 'Enter') {
            if (activeIdx >= 0 && items[activeIdx]) {
                e.preventDefault();
                input.value = items[activeIdx].textContent;
                list.style.display = 'none';
                form.submit();
            }
            return;
        } else if (e.key === 'Escape') {
            list.style.display = 'none';
            return;
        }

        items.forEach((item, i) => item.classList.toggle('active', i === activeIdx));
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target)) list.style.display = 'none';
    });
</script>

@endsection
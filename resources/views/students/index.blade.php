@extends('layouts.dashboard')

@section('content')

@php
    function sort_link($column, $sort, $direction)
    {
        return request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => ($sort === $column && $direction === 'asc') ? 'desc' : 'asc'
        ]);
    }

    function sort_icon($column, $sort, $direction)
    {
        if ($sort !== $column) {
            return '<i class="fas fa-sort text-secondary ms-1"></i>';
        }

        return $direction === 'asc'
            ? '<i class="fas fa-sort-up text-warning ms-1"></i>'
            : '<i class="fas fa-sort-down text-warning ms-1"></i>';
    }
@endphp



<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ── Page hero ─────────────────────────────────────────────────────────── */
.page-hero {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-radius: 12px;
    color: #fff;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
    pointer-events: none;
}
.page-hero h2 { font-size: 1.35rem; font-weight: 700; margin: 0 0 4px; }
.page-hero p  { margin: 0; opacity: .7; font-size: .85rem; }

.hero-stat {
    background: rgba(255,255,255,.1);
    border-radius: 8px;
    padding: 10px 18px;
    text-align: center;
    min-width: 80px;
}
.hero-stat .num { font-size: 1.4rem; font-weight: 700; line-height: 1; }
.hero-stat .lbl { font-size: .7rem; opacity: .7; margin-top: 2px; }

/* ── Search card ───────────────────────────────────────────────────────── */
.filter-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    margin-bottom: 20px;
}
.filter-card .card-body { padding: 16px 20px; }

/* ── Students table ────────────────────────────────────────────────────── */
.students-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    overflow: hidden;
}
.students-card .card-header {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.students-card .card-header h5 {
    margin: 0; font-weight: 700; font-size: .95rem; color: #1e293b;
}

.tbl thead th {
    background: #1e293b;
    color: #fff;
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .4px;
    padding: 11px 14px;
    border: none;
    white-space: nowrap;
}
.tbl tbody td {
    padding: 10px 14px;
    vertical-align: middle;
    font-size: .855rem;
    border-color: #f1f5f9;
}
.tbl tbody tr:hover { background: #f8fafc; }

.avatar-circle {
    width: 42px; height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}
.avatar-initials {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

.adm-no {
    font-family: monospace;
    font-size: .78rem;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
}

.btn-action { border-radius: 6px; font-size: .78rem; font-weight: 600; padding: 5px 12px; }

/* ── Pagination ────────────────────────────────────────────────────────── */
.pa-pagination-wrap {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    padding: 12px 20px;
    margin-top: 16px;
}
.pa-pagination-wrap p { margin: 0; font-size: .82rem; }
.pa-pagination-wrap .pagination { margin: 0; gap: 4px; }
.pa-pagination-wrap .page-link {
    border: none;
    border-radius: 8px !important;
    color: #334155;
    font-size: .85rem;
    font-weight: 600;
    min-width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
    margin: 0;
    box-shadow: none;
}
.pa-pagination-wrap .page-link:hover { background: #eff6ff; color: #1d4ed8; }
.pa-pagination-wrap .page-item.active .page-link {
    background: #1e293b;
    color: #fff;
}
.pa-pagination-wrap .page-item.disabled .page-link { color: #cbd5e1; background: transparent; }
</style>

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h2><i class="fas fa-user-graduate me-2" style="opacity:.8;"></i>Student Management</h2>
            <p>Manage admissions, profiles, classes and sessions</p>
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap">

            {{-- Stats --}}
            <div class="hero-stat">
                <div class="num">{{ $students->total() }}</div>
                <div class="lbl">Total</div>
            </div>

            {{-- Action buttons --}}
            <div class="d-flex gap-2">
                <a href="{{ route('students.import') }}" class="btn btn-sm btn-outline-light" style="border-radius:8px; font-weight:600;">
                    <i class="fas fa-file-import me-1"></i> Bulk Import
                </a>
                <a href="{{ route('students.create') }}" class="btn btn-sm btn-warning" style="border-radius:8px; font-weight:600; color:#1e293b;">
                    <i class="fas fa-plus me-1"></i> New Admission
                </a>

<a href="{{ route('students.export', request()->query()) }}"
   class="btn btn-success btn-sm">
    <i class="fas fa-file-excel"></i>
    Export Excel
</a>



            </div>

        </div>

    </div>

</div>

{{-- ── Flash messages ───────────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:8px; font-size:.875rem;">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="alert alert-warning alert-dismissible fade show" style="border-radius:8px; font-size:.875rem;">
        <strong><i class="fas fa-exclamation-triangle me-1"></i>Some rows were skipped:</strong>
        <ul class="mb-0 mt-1 small">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($familyCodeFilter)
    <div class="alert alert-dismissible fade show d-flex justify-content-between align-items-center flex-wrap gap-2"
         style="border-radius:8px; font-size:.875rem; background:#fce7f3; color:#9d174d; border:1px solid #fbcfe8;">
        <div>
            <i class="fas fa-people-roof me-1"></i>
            Showing family <strong>{{ $familyCodeFilter }}</strong> —
            Combined Outstanding:
            <strong>Rs {{ number_format($familyOutstanding ?? 0) }}</strong>
        </div>
        <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
            Clear Family Filter
        </a>
    </div>
@endif

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<div class="filter-card card">
    <div class="card-body">
        <form method="GET" action="{{ route('students.index') }}">
            <div class="row g-2">

                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:8px 0 0 8px;">
                            <i class="fas fa-search text-muted" style="font-size:.8rem;"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control border-start-0 ps-0"
                               style="border-radius:0 8px 8px 0;"
                               placeholder="Search by name, admission no, father name…">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="class_id" class="form-select" style="border-radius:8px; font-size:.85rem;">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="session_id" class="form-select" style="border-radius:8px; font-size:.85rem;">
                        <option value="">All Sessions</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                {{ $session->session_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select" style="border-radius:8px; font-size:.85rem;">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-1 d-grid">
                    <button class="btn btn-dark" style="border-radius:8px; font-size:.85rem;">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Students table ───────────────────────────────────────────────────── --}}
<div class="students-card card">

    <div class="card-header">
        <h5><i class="fas fa-list me-2 text-muted" style="font-size:.85rem;"></i>Students List</h5>
        <small class="text-muted">
            Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }}
        </small>
    </div>

    <div class="table-responsive">
        <table class="table tbl mb-0">

            <<thead>
<tr>

    <th width="42">#</th>

    <th width="52">Photo</th>

    <th>
        <a href="{{ sort_link('admission_no',$sort,$direction) }}"
           class="text-white text-decoration-none">
            Admission No
            {!! sort_icon('admission_no',$sort,$direction) !!}
        </a>
    </th>

    <th>
        <a href="{{ sort_link('name',$sort,$direction) }}"
           class="text-white text-decoration-none">
            Student
            {!! sort_icon('name',$sort,$direction) !!}
        </a>
    </th>

    <th>
        <a href="{{ sort_link('class',$sort,$direction) }}"
           class="text-white text-decoration-none">
            Class
            {!! sort_icon('class',$sort,$direction) !!}
        </a>
    </th>

    <th>
        <a href="{{ sort_link('session',$sort,$direction) }}"
           class="text-white text-decoration-none">
            Session
            {!! sort_icon('session',$sort,$direction) !!}
        </a>
    </th>

    <th>Contact</th>

    <th width="70">
        <a href="{{ sort_link('status',$sort,$direction) }}"
           class="text-white text-decoration-none">
            Status
            {!! sort_icon('status',$sort,$direction) !!}
        </a>
    </th>

    <th width="160">Actions</th>

</tr>
</thead>
            <tbody>
                @forelse($students as $index => $student)
                    @php $enrollment = $student->enrollments->last(); @endphp
                    <tr>

                        <td class="text-muted" style="font-size:.78rem;">
                            {{ $students->firstItem() + $index }}
                        </td>

                        <td>
                            @if($student->photo_url)
                                <img src="{{ $student->photo_url }}"
                                     class="avatar-circle" alt="" loading="lazy"
                                     onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('span'),{className:'avatar-initials',textContent:'{{ strtoupper(substr($student->student_name, 0, 1)) }}'}));">
                            @else
                                <span class="avatar-initials">
                                    {{ strtoupper(substr($student->student_name, 0, 1)) }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <span class="adm-no">{{ $student->admission_no }}</span>
                        </td>

                        <td>
                            <div class="fw-bold" style="color:#1e293b;">{{ $student->student_name }}</div>
                            @if($student->father_name)
                                <small class="text-muted">
                                    <i class="fas fa-user-tie me-1" style="font-size:.65rem;"></i>{{ $student->father_name }}
                                </small>
                            @endif
                            @if($student->gender)
                                <small class="text-muted d-block" style="font-size:.7rem;">
                                    {{ $student->gender }}
                                </small>
                            @endif
                            @if($student->family_code)
                                <a href="{{ route('students.index', ['family_code' => $student->family_code]) }}"
                                   class="badge d-inline-block mt-1"
                                   style="background:#fce7f3; color:#9d174d; font-size:.68rem; font-weight:600; padding:3px 7px; border-radius:5px; text-decoration:none;"
                                   title="View family / siblings">
                                    <i class="fas fa-people-roof me-1"></i>{{ $student->family_code }}
                                </a>
                            @endif
                        </td>

                        <td>
                            @if($enrollment && $enrollment->class)
                                <span class="badge" style="background:#dcfce7; color:#166534; font-size:.75rem; font-weight:600; padding:4px 9px; border-radius:5px;">
                                    {{ $enrollment->class->class_name }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($enrollment && $enrollment->session)
                                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:.75rem; font-weight:600; padding:4px 9px; border-radius:5px;">
                                    {{ $enrollment->session->session_name }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($student->mobile_no)
                                <div style="font-size:.82rem;">
                                    <i class="fas fa-mobile-alt text-muted me-1" style="font-size:.7rem;"></i>{{ $student->mobile_no }}
                                    <small class="text-muted">(Father)</small>
                                </div>
                            @endif
                            @if($student->mother_mobile_no)
                                <div style="font-size:.82rem;">
                                    <i class="fas fa-mobile-alt text-muted me-1" style="font-size:.7rem;"></i>{{ $student->mother_mobile_no }}
                                    <small class="text-muted">(Mother)</small>
                                </div>
                            @endif
                            @if($student->guardian_mobile && $student->guardian_mobile !== $student->mobile_no && $student->guardian_mobile !== $student->mother_mobile_no)
                                <small class="text-muted d-block">{{ $student->guardian_mobile }}</small>
                            @endif
                            @if(!$student->mobile_no && !$student->mother_mobile_no && !$student->guardian_mobile)
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($student->is_active)
                                <span class="badge" style="background:#dcfce7; color:#166534; font-size:.72rem; padding:4px 8px; border-radius:5px;">Active</span>
                            @else
                                <span class="badge" style="background:#fee2e2; color:#991b1b; font-size:.72rem; padding:4px 8px; border-radius:5px;">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('students.show', $student->id) }}"
                                   class="btn btn-action"
                                   style="background:#eff6ff; color:#1d4ed8; border:none;"
                                   title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('students.edit', $student->id) }}"
                                   class="btn btn-action"
                                   style="background:#fef9c3; color:#854d0e; border:none;"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('students.destroy', $student->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete {{ addslashes($student->student_name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-action"
                                            style="background:#fee2e2; color:#991b1b; border:none;"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-user-slash fa-2x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">No students found matching your filters.</p>
                            @if(request()->anyFilled(['search','class_id','session_id','status']))
                                <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
                                    Clear filters
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
@if($students->hasPages())
    <div class="pa-pagination-wrap">
        {{ $students->links() }}
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection
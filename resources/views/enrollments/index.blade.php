@extends('layouts.dashboard')

@section('content')

<style>
.enroll-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;
}
.enroll-avatar-initials {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .85rem; flex-shrink: 0;
}
.adm-no {
    font-family: monospace; font-size: .74rem;
    background: #eff6ff; color: #1d4ed8;
    padding: 1px 7px; border-radius: 4px; font-weight: 600;
}
.filter-card { border: none; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,.07); margin-bottom: 20px; }
.filter-card .card-body { padding: 16px 20px; }
</style>

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h2><i class="fas fa-clipboard-list me-2" style="opacity:.8;"></i>Student Enrollments</h2>
            <p>Manage class &amp; session enrollments, promotions and roll numbers</p>
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap">

            <div class="hero-stat">
                <div class="num">{{ $stats['total'] }}</div>
                <div class="lbl">Total</div>
            </div>
            <div class="hero-stat">
                <div class="num">{{ $stats['active'] }}</div>
                <div class="lbl">Active</div>
            </div>
            <div class="hero-stat">
                <div class="num">{{ $stats['inactive'] }}</div>
                <div class="lbl">Inactive</div>
            </div>
            <div class="hero-stat">
                <div class="num">{{ $stats['sessions'] }}</div>
                <div class="lbl">Live Sessions</div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('enrollments.export', request()->query()) }}" class="btn-hero-ghost">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
                <a href="{{ route('enrollments.create') }}" class="btn btn-sm btn-warning" style="border-radius:8px; font-weight:600; color:#1e293b;">
                    <i class="fas fa-plus me-1"></i> New Enrollment
                </a>
            </div>

        </div>

    </div>
</div>

{{-- ── Flash messages ───────────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<div class="filter-card card">
    <div class="card-body">
        <form method="GET" action="{{ route('enrollments.index') }}" id="filterForm">
            <div class="row g-2">

                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:8px 0 0 8px;">
                            <i class="fas fa-search text-muted" style="font-size:.8rem;"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control border-start-0 ps-0"
                               style="border-radius:0 8px 8px 0;"
                               placeholder="Search student, admission no, father name…">
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
                    <select name="session_id" class="form-select filter-auto">
                        <option value="">All Sessions</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                {{ $session->session_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select filter-auto">
                        <option value="">All Status</option>
                        <option value="active"     {{ request('status') === 'active'     ? 'selected' : '' }}>Active</option>
                        <option value="inactive"   {{ request('status') === 'inactive'   ? 'selected' : '' }}>Inactive</option>
                        <option value="left"       {{ request('status') === 'left'       ? 'selected' : '' }}>Left</option>
                        <option value="passed_out" {{ request('status') === 'passed_out' ? 'selected' : '' }}>Passed Out</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark flex-grow-1" style="border-radius:8px; font-size:.85rem;">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search','class_id','session_id','status']))
                        <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;" title="Clear filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Enrollments table ───────────────────────────────────────────────── --}}
<div class="section-card card">

    <div class="card-header">
        <span class="s-icon" style="background:#1e293b;"><i class="fas fa-list"></i></span>
        <h6>Enrollment Records</h6>
        <small class="text-muted ms-auto">
            Showing {{ $enrollments->firstItem() ?? 0 }}–{{ $enrollments->lastItem() ?? 0 }} of {{ $enrollments->total() }}
        </small>
    </div>

    <div class="table-responsive">
        <table class="table pa-table mb-0">
            <thead>
                <tr>
                    <th width="42">#</th>
                    <th width="46">Photo</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Session</th>
                    <th>Roll No</th>
                    <th>Enrollment Date</th>
                    <th>Status</th>
                    <th width="190">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $i => $enrollment)
                    <tr>
                        <td class="text-muted" style="font-size:.78rem;">
                            {{ $enrollments->firstItem() + $i }}
                        </td>

                        <td>
                            @if($enrollment->student && $enrollment->student->photo_url)
                                <img src="{{ $enrollment->student->photo_url }}" class="enroll-avatar" alt=""
                                     onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('span'),{className:'enroll-avatar-initials',textContent:'{{ $enrollment->student ? strtoupper(substr($enrollment->student->student_name,0,1)) : '?' }}'}));">
                            @else
                                <span class="enroll-avatar-initials">
                                    {{ $enrollment->student ? strtoupper(substr($enrollment->student->student_name, 0, 1)) : '?' }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($enrollment->student)
                                <div class="fw-bold" style="color:#1e293b;">
                                    <a href="{{ route('students.show', $enrollment->student->id) }}" class="text-decoration-none" style="color:#1e293b;">
                                        {{ $enrollment->student->student_name }}
                                    </a>
                                </div>
                                <span class="adm-no">{{ $enrollment->student->admission_no }}</span>
                            @else
                                <span class="text-muted">Student not found</span>
                            @endif
                        </td>

                        <td>
                            <span class="pa-badge pa-badge-green">{{ $enrollment->class->class_name ?? '—' }}</span>
                        </td>

                        <td>
                            <span class="pa-badge pa-badge-blue">{{ $enrollment->session->session_name ?? '—' }}</span>
                        </td>

                        <td>{{ $enrollment->roll_no ?? '—' }}</td>

                        <td class="text-muted" style="white-space:nowrap; font-size:.82rem;">
                            @if($enrollment->enrollment_date)
                                {{ \Carbon\Carbon::parse($enrollment->enrollment_date)->format('d M Y') }}
                            @else —
                            @endif
                        </td>

                        <td>
                            @php
                                $statusMap = [
                                    'active'     => 'pa-badge-green',
                                    'inactive'   => 'pa-badge-gray',
                                    'left'       => 'pa-badge-red',
                                    'passed_out' => 'pa-badge-blue',
                                ];
                                $st = $enrollment->status ?? ($enrollment->is_active ? 'active' : 'inactive');
                            @endphp
                            <span class="pa-badge {{ $statusMap[$st] ?? 'pa-badge-gray' }}">
                                {{ ucfirst(str_replace('_',' ', $st)) }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('enrollments.edit', $enrollment->id) }}"
                                   class="btn-icon btn-icon-yellow" title="Edit Enrollment">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('enrollments.toggle', $enrollment->id) }}" method="POST"
                                      onsubmit="return confirm('{{ $st === 'active' ? 'Deactivate' : 'Activate' }} this enrollment?')">
                                    @csrf @method('PATCH')
                                    <button class="btn-icon {{ $st === 'active' ? 'btn-icon-gray' : 'btn-icon-green' }}"
                                            title="{{ $st === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $st === 'active' ? 'toggle-off' : 'toggle-on' }}"></i>
                                    </button>
                                </form>

                                <form action="{{ route('enrollments.destroy', $enrollment->id) }}" method="POST"
                                      onsubmit="return confirm('Delete this enrollment record? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="btn-icon btn-icon-red" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-inbox fa-2x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">No enrollments found matching your filters.</p>
                            @if(request()->anyFilled(['search','class_id','session_id','status']))
                                <a href="{{ route('enrollments.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
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
@if($enrollments->hasPages())
    <div class="pa-pagination-wrap">
        {{ $enrollments->links() }}
    </div>
@endif

@push('scripts')
<script>
    // Auto-submit the filter form when a dropdown changes — feels more
    // interactive than requiring an explicit "Filter" click every time.
    document.querySelectorAll('.filter-auto').forEach(function (el) {
        el.addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@endpush

@endsection
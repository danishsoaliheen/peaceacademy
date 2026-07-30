@extends('layouts.dashboard')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                Class Management
            </h2>
            <p class="text-muted mb-0">
                Manage classes, order, and active status
            </p>
        </div>

        <a href="{{ route('classes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i>
            Add New Class
        </a>

    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-primary">{{ $classes->count() }}</div>
                <div class="text-muted small">Total Classes</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-success">{{ $classes->where('is_active', 1)->count() }}</div>
                <div class="text-muted small">Active Classes</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-warning">{{ $classes->where('is_active', 0)->count() }}</div>
                <div class="text-muted small">Inactive Classes</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-info">{{ $classes->sum('active_students_count') }}</div>
                <div class="text-muted small">Total Enrolled Students</div>
            </div>
        </div>

    </div>

    {{-- Classes Table --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">All Classes</h5>
            <small class="text-muted">
                <i class="fas fa-grip-vertical me-1"></i>
                Drag rows to reorder
            </small>
        </div>

        <div class="card-body p-0">

            <table class="table table-hover align-middle mb-0" id="classesTable">

                <thead class="table-dark">
                    <tr>
                        <th width="40" class="text-center">⠿</th>
                        <th width="60">#</th>
                        <th>Class Name</th>
                        <th>Code</th>
                        <th class="text-center">Order</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>

                <tbody id="sortableClasses">

                    @forelse($classes as $class)

                        <tr data-id="{{ $class->id }}" style="cursor: grab;">

                            {{-- Drag Handle --}}
                            <td class="text-center text-muted">
                                <i class="fas fa-grip-vertical"></i>
                            </td>

                            {{-- Row Number --}}
                            <td class="text-muted">{{ $loop->iteration }}</td>

                            {{-- Class Name --}}
                            <td>
                                <span class="fw-bold fs-6">{{ $class->class_name }}</span>
                            </td>

                            {{-- Code --}}
                            <td>
                                @if($class->class_code)
                                    <span class="badge bg-secondary px-3">{{ $class->class_code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Order --}}
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    {{ $class->class_order }}
                                </span>
                            </td>

                            {{-- Students --}}
                            <td class="text-center">
                                @if($class->active_students_count > 0)
                                    <a href="{{ route('students.index') }}?class_id={{ $class->id }}"
                                       class="badge bg-success text-white text-decoration-none px-3 py-2">
                                        {{ $class->active_students_count }} students
                                    </a>
                                @else
                                    <span class="badge bg-light text-muted border">0 students</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($class->is_active)
                                    <span class="badge bg-success px-3 py-2">Active</span>
                                @else
                                    <span class="badge bg-danger px-3 py-2">Inactive</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="d-flex gap-1">

                                    {{-- Edit --}}
                                    <a href="{{ route('classes.edit', $class->id) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    {{-- Toggle Status --}}
                                    <form action="{{ route('classes.toggle', $class->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $class->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                title="{{ $class->is_active ? 'Deactivate' : 'Activate' }}">
                                            {{ $class->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form action="{{ route('classes.destroy', $class->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete {{ $class->class_name }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-school fa-3x mb-3 d-block"></i>
                                    No classes found.
                                    <a href="{{ route('classes.create') }}">Add the first class</a>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

{{-- SortableJS for drag-to-reorder --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const tbody = document.getElementById('sortableClasses');

    if (tbody) {
        Sortable.create(tbody, {
            animation: 150,
            handle: '.fa-grip-vertical',
            ghostClass: 'table-active',
            onEnd: function () {
                const rows  = tbody.querySelectorAll('tr[data-id]');
                const order = Array.from(rows).map(r => r.dataset.id);

                fetch('{{ route('classes.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Update the order badge values in place
                        rows.forEach((row, i) => {
                            const badge = row.querySelector('.badge.bg-light.text-dark.border');
                            if (badge) badge.textContent = i + 1;
                        });
                    }
                });
            }
        });
    }
</script>

@endsection

@extends('layouts.dashboard')

@section('content')
<style>
.perm-hero { background:linear-gradient(135deg,#1e293b 0%,#334155 100%); border-radius:12px; color:#fff; padding:24px 30px; margin-bottom:22px; }
.perm-table thead th { background:#1e293b; color:#fff; font-size:.78rem; font-weight:600; padding:11px 16px; border:none; white-space:nowrap; }
.perm-table tbody td { padding:9px 16px; vertical-align:middle; font-size:.85rem; border-color:#f1f5f9; }
.perm-group-row td { background:#f1f5f9; font-weight:700; font-size:.78rem; text-transform:uppercase; letter-spacing:.4px; color:#475569; padding:8px 16px; }
.perm-check { width:18px; height:18px; cursor:pointer; }
.role-col-head { text-align:center; }
.admin-note { background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; border-radius:8px; padding:12px 16px; font-size:.82rem; margin-bottom:18px; }
.select-all-link { font-size:.72rem; font-weight:600; cursor:pointer; color:#1d4ed8; text-decoration:underline; }
</style>

<div class="perm-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h2 class="mb-1" style="font-size:1.25rem;font-weight:700;"><i class="fas fa-shield-halved me-2"></i>Role Permissions</h2>
        <p class="mb-0" style="opacity:.68;font-size:.82rem;">Control which pages and actions each role can access</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn-hero-ghost"><i class="fas fa-arrow-left"></i> Back to Users</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><strong>Please fix the following:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="admin-note">
    <i class="fas fa-circle-info me-1"></i>
    The <strong>Administrator</strong> role is not shown below — admins always have full access to every
    page and action, regardless of this table.
</div>

<form method="POST" action="{{ route('permissions.update') }}">
@csrf
@method('PUT')

<div class="section-card card">
    <div class="card-header" style="justify-content:space-between;">
        <div class="d-flex align-items-center gap-2">
            <span class="s-icon" style="background:#1e293b;"><i class="fas fa-table-list"></i></span>
            <h6>Permission Matrix</h6>
        </div>
        <button type="submit" class="btn-save" style="padding:8px 18px;font-size:.82rem;">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>

    <div class="table-responsive">
        <table class="table perm-table mb-0">
            <thead>
                <tr>
                    <th>Permission</th>
                    @foreach($roles as $roleKey => $roleLabel)
                        <th class="role-col-head" width="140">
                            {{ $roleLabel }}
                            <div class="mt-1">
                                <span class="select-all-link" data-role="{{ $roleKey }}" data-action="all">All</span>
                                &middot;
                                <span class="select-all-link" data-role="{{ $roleKey }}" data-action="none">None</span>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $groupName => $groupPermissions)
                    <tr class="perm-group-row">
                        <td colspan="{{ count($roles) + 1 }}">
                            {{ $groupName ?? 'Ungrouped' }}
                        </td>
                    </tr>
                    @foreach($groupPermissions as $permission)
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#1e293b;">{{ $permission->name }}</div>
                                @if($permission->description)
                                    <div class="text-muted" style="font-size:.76rem;">{{ $permission->description }}</div>
                                @endif
                            </td>
                            @foreach($roles as $roleKey => $roleLabel)
                                <td class="text-center">
                                    <input type="checkbox"
                                           class="perm-check role-{{ $roleKey }}"
                                           name="roles[{{ $roleKey }}][]"
                                           value="{{ $permission->id }}"
                                           {{ in_array($permission->id, $assignedMap[$roleKey] ?? []) ? 'checked' : '' }}>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ count($roles) + 1 }}" class="text-center py-5 text-muted">
                            No permissions found. Run the RbacSeeder first.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-body border-top d-flex justify-content-end">
        <button type="submit" class="btn-save" style="padding:10px 24px;">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</div>

</form>

@push('scripts')
<script>
    document.querySelectorAll('.select-all-link').forEach(function (link) {
        link.addEventListener('click', function () {
            const role   = this.dataset.role;
            const action = this.dataset.action;
            document.querySelectorAll('.role-' + role).forEach(function (cb) {
                cb.checked = (action === 'all');
            });
        });
    });
</script>
@endpush

@endsection
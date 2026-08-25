@extends('layouts.dashboard')

@section('content')
<style>
.users-hero { background: linear-gradient(135deg,#1e293b 0%,#334155 100%); border-radius:12px; color:#fff; padding:24px 30px; margin-bottom:22px; position:relative; overflow:hidden; }
.users-hero::before { content:''; position:absolute; top:-55px; right:-55px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,.05); pointer-events:none; }
.users-hero h2 { font-size:1.25rem; font-weight:700; margin:0 0 4px; }
.users-hero p { margin:0; opacity:.68; font-size:.82rem; }
.user-stat { background:rgba(255,255,255,.1); border-radius:8px; padding:9px 16px; text-align:center; min-width:72px; }
.user-stat .num { font-size:1.3rem; font-weight:700; line-height:1; }
.user-stat .lbl { font-size:.67rem; opacity:.7; margin-top:2px; }
.users-card { border:none; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,.07); overflow:hidden; }
.users-card .card-header { background:#f8fafc; border-bottom:2px solid #e2e8f0; padding:12px 20px; display:flex; align-items:center; justify-content:space-between; }
.users-table thead th { background:#1e293b; color:#fff; font-size:.76rem; font-weight:600; letter-spacing:.35px; padding:11px 14px; border:none; white-space:nowrap; }
.users-table tbody td { padding:10px 14px; vertical-align:middle; font-size:.85rem; border-color:#f1f5f9; }
.users-table tbody tr:hover { background:#f8fafc; }
.user-avatar { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; }
.filter-card { border:none; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,.07); margin-bottom:20px; }
</style>

<div class="users-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-users-cog me-2" style="opacity:.8;"></i>User Management</h2>
            <p>Manage staff accounts, roles and access status</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap" style="position:relative;z-index:2;">
            <div class="user-stat"><div class="num">{{ $counts['all'] }}</div><div class="lbl">Total</div></div>
            <div class="user-stat"><div class="num">{{ $counts['active'] }}</div><div class="lbl">Active</div></div>
            <div class="user-stat"><div class="num">{{ $counts['inactive'] }}</div><div class="lbl">Inactive</div></div>
            <a href="{{ route('users.create') }}" class="btn btn-warning btn-sm" style="border-radius:8px;font-weight:600;color:#1e293b;position:relative;z-index:3;">
                <i class="fas fa-plus me-1"></i> New User
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="filter-card card">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Search by name or email…">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $key => $label)<option value="{{ $key }}" @selected($role === $key)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        <option value="all" @selected($status === 'all')>All Status</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-dark"><i class="fas fa-filter me-1"></i> Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="users-card card">
    <div class="card-header">
        <h5 class="mb-0" style="font-weight:700;font-size:.95rem;"><i class="fas fa-list me-2 text-muted"></i>System Users</h5>
        <small class="text-muted">{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</small>
    </div>
    <div class="table-responsive">
        <table class="table users-table mb-0">
            <thead><tr><th width="45">#</th><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th width="180">Actions</th></tr></thead>
            <tbody>
            @forelse($users as $index => $user)
                <tr>
                    <td class="text-muted">{{ $users->firstItem() + $index }}</td>
                    <td><div class="d-flex align-items-center gap-2"><span class="user-avatar">{{ strtoupper(substr($user->name,0,1)) }}</span><div><div class="fw-bold">{{ $user->name }}</div>@if(auth()->id() === $user->id)<small class="text-muted">You</small>@endif</div></div></td>
                    <td>{{ $user->email }}</td>
                    <td><span class="pa-badge pa-badge-blue">{{ $roles[$user->role] ?? ucfirst($user->role) }}</span></td>
                    <td>@if($user->is_active)<span class="pa-badge pa-badge-green">Active</span>@else<span class="pa-badge pa-badge-red">Inactive</span>@endif</td>
                    <td>{{ optional($user->created_at)->format('d M Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('users.edit',$user) }}" class="btn-icon btn-icon-blue"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('users.toggle',$user) }}" class="d-inline" onsubmit="return confirm('{{ $user->is_active ? 'Deactivate this user?' : 'Activate this user?' }}');">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-icon {{ $user->is_active ? 'btn-icon-red' : 'btn-icon-green' }}"><i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-users-slash fa-2x mb-2"></i><br>No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($users->hasPages())
<div class="pa-pagination-wrap d-flex justify-content-between align-items-center flex-wrap gap-2">
    <p class="text-muted">Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }}</p>
    {{ $users->links() }}
</div>
@endif
@endsection
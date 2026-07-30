@extends('layouts.dashboard')

@section('content')

@php $activeSession = $sessions->firstWhere('is_active', 1); @endphp

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h2><i class="fas fa-calendar-alt me-2" style="opacity:.8;"></i>Academic Sessions</h2>
            <p>Manage academic years and set the current active session</p>
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="hero-stat">
                <div class="num">{{ $sessions->count() }}</div>
                <div class="lbl">Total</div>
            </div>
            <div class="hero-stat">
                <div class="num">{{ $sessions->where('is_active', 1)->count() }}</div>
                <div class="lbl">Active</div>
            </div>
            <a href="{{ route('sessions.create') }}" class="btn-hero-ghost">
                <i class="fas fa-plus"></i> New Session
            </a>
        </div>

    </div>
</div>

{{-- ── Flash messages ───────────────────────────────────────────────────── --}}
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

{{-- ── Active session banner ────────────────────────────────────────────── --}}
@if($activeSession)
    <div class="d-flex align-items-center gap-3 p-3 mb-3 rounded-3"
         style="background:#f0fdf4; border:1.5px solid #bbf7d0;">
        <div style="width:38px;height:38px;background:#22c55e;border-radius:9px;
                    display:flex;align-items:center;justify-content:center;
                    flex-shrink:0;color:#fff;font-size:15px;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div style="flex:1;">
            <div style="font-weight:700;color:#166534;font-size:.88rem;">
                Current Session: {{ $activeSession->session_name }}
            </div>
            <div style="font-size:.78rem;color:#4ade80;">
                @if($activeSession->start_date)
                    {{ $activeSession->start_date->format('d M Y') }}
                    &nbsp;–&nbsp;
                    {{ $activeSession->end_date?->format('d M Y') ?? 'Ongoing' }}
                    &nbsp;&nbsp;·&nbsp;&nbsp;
                @endif
                <strong>{{ $activeSession->enrolled_students_count }}</strong> students enrolled
            </div>
        </div>
    </div>
@endif

{{-- ── Sessions table ───────────────────────────────────────────────────── --}}
<div class="section-card card">

    <div class="card-header" style="justify-content:space-between;">
        <div class="d-flex align-items-center gap-2">
            <span class="s-icon" style="background:#0f172a;"><i class="fas fa-list"></i></span>
            <h6>All Sessions</h6>
        </div>
        <small class="text-muted">{{ $sessions->count() }} session(s)</small>
    </div>

    <div class="table-responsive">
        <table class="table pa-table mb-0">
            <thead>
                <tr>
                    <th width="44">#</th>
                    <th>Session Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Duration</th>
                    <th class="text-center">Students</th>
                    <th class="text-center">Status</th>
                    <th width="210">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr style="{{ $session->is_active ? 'background:#f0fdf4;' : '' }}">

                        <td class="text-muted" style="font-size:.76rem;">{{ $loop->iteration }}</td>

                        {{-- Name --}}
                        <td>
                            <span class="fw-bold" style="color:#1e293b;">{{ $session->session_name }}</span>
                            @if($session->is_active)
                                <span class="pa-badge pa-badge-green ms-2">
                                    <i class="fas fa-circle me-1" style="font-size:.45rem;vertical-align:middle;"></i>Current
                                </span>
                            @endif
                        </td>

                        {{-- Start --}}
                        <td>
                            @if($session->start_date)
                                <span style="color:#475569;">{{ $session->start_date->format('d M Y') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- End --}}
                        <td>
                            @if($session->end_date)
                                <span style="color:#475569;">{{ $session->end_date->format('d M Y') }}</span>
                            @else
                                <span class="pa-badge pa-badge-blue">Ongoing</span>
                            @endif
                        </td>

                        {{-- Duration --}}
                        <td>
                            @if($session->start_date && $session->end_date)
                                @php $months = $session->start_date->diffInMonths($session->end_date); @endphp
                                <span class="text-muted" style="font-size:.8rem;">{{ $months }} months</span>
                            @elseif($session->start_date)
                                @php $months = $session->start_date->diffInMonths(now()); @endphp
                                <span class="text-muted" style="font-size:.8rem;">{{ $months }}m so far</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Students --}}
                        <td class="text-center">
                            @if($session->enrolled_students_count > 0)
                                <a href="{{ route('students.index') }}?session_id={{ $session->id }}"
                                   class="pa-badge pa-badge-blue text-decoration-none">
                                    {{ $session->enrolled_students_count }}
                                    <i class="fas fa-external-link-alt ms-1" style="font-size:.6rem;"></i>
                                </a>
                            @else
                                <span class="pa-badge pa-badge-gray">0</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if($session->is_active)
                                <span class="pa-badge pa-badge-green">Active</span>
                            @else
                                <span class="pa-badge pa-badge-gray">Inactive</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="d-flex gap-1 flex-wrap">

                                <a href="{{ route('sessions.edit', $session->id) }}"
                                   class="btn-icon btn-icon-yellow">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                @if(!$session->is_active)
                                    <form action="{{ route('sessions.set-active', $session->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn-icon btn-icon-green"
                                                onclick="return confirm('Set \'{{ addslashes($session->session_name) }}\' as the current session?')">
                                            <i class="fas fa-check-circle"></i> Set Current
                                        </button>
                                    </form>
                                @endif

                                @if(!$session->is_active)
                                    <form action="{{ route('sessions.destroy', $session->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete \'{{ addslashes($session->session_name) }}\'? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="btn-icon btn-icon-red">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <button class="btn-icon btn-icon-gray" disabled
                                            title="Cannot delete the active session" style="opacity:.45;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-calendar fa-2x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-2">No sessions found.</p>
                            <a href="{{ route('sessions.create') }}" class="btn-icon btn-icon-blue">
                                <i class="fas fa-plus"></i> Add First Session
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection

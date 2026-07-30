<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peace Academy ERP</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f1f5f9;
        color: #1e293b;
        font-size: .9rem;
    }

    .wrapper { display: flex; min-height: 100vh; }

    /* ── Sidebar ─────────────────────────────────────────────────── */
    .sidebar {
        width: 232px; min-width: 232px;
        background: #0f172a;
        position: sticky; top: 0; height: 100vh;
        overflow-y: auto; display: flex;
        flex-direction: column; z-index: 200;
        scrollbar-width: none;
    }
    .sidebar::-webkit-scrollbar { display: none; }

    .sidebar-brand {
        padding: 20px 18px 16px;
        border-bottom: 1px solid rgba(255,255,255,.06);
        flex-shrink: 0;
    }
    .sidebar-brand .logo-row { display: flex; align-items: center; gap: 10px; }
    .sidebar-brand .logo-icon {
        width: 34px; height: 34px; background: #3b82f6; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; color: #fff; flex-shrink: 0;
    }
    .sidebar-brand .logo-text { font-size: 1rem; font-weight: 700; color: #fff; letter-spacing: .3px; }
    .sidebar-brand .logo-sub  { font-size: .65rem; color: #475569; margin-top: 1px; }

    .nav-section {
        font-size: .63rem; font-weight: 700; letter-spacing: 1.1px;
        text-transform: uppercase; color: #475569;
        padding: 16px 18px 5px;
    }

    .sidebar a {
        display: flex; align-items: center; gap: 10px;
        color: #94a3b8; text-decoration: none;
        padding: 9px 18px; font-size: .82rem; font-weight: 500;
        transition: all .14s; border-left: 3px solid transparent;
    }
    .sidebar a .nav-icon { width: 16px; text-align: center; font-size: .78rem; flex-shrink: 0; opacity: .8; }
    .sidebar a:hover    { color: #e2e8f0; background: rgba(255,255,255,.05); }
    .sidebar a.active   { color: #fff; background: rgba(59,130,246,.14); border-left-color: #3b82f6; }
    .sidebar a.active .nav-icon { opacity: 1; }

    /* ── Main ────────────────────────────────────────────────────── */
    .main { flex: 1; display: flex; flex-direction: column; min-width: 0; }

    .topbar {
        background: #fff; border-bottom: 1px solid #e2e8f0;
        padding: 0 26px; height: 54px;
        display: flex; align-items: center; justify-content: space-between;
        position: sticky; top: 0; z-index: 100; flex-shrink: 0;
    }
    .topbar-left  { font-size: .84rem; font-weight: 600; color: #1e293b; }
    .topbar-right { font-size: .76rem; color: #64748b; display: flex; align-items: center; gap: 14px; }

    .content-area { padding: 24px 26px; flex: 1; }

    /* ═══════════════════════════════════════════════════════════════
       SHARED COMPONENTS — inherited by every page
       ═══════════════════════════════════════════════════════════════ */

    /* Page hero */
    .page-hero {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-radius: 12px; color: #fff;
        padding: 24px 30px; margin-bottom: 22px;
        position: relative; overflow: hidden;
    }
    .page-hero::before {
        content: ''; position: absolute;
        top: -55px; right: -55px;
        width: 200px; height: 200px;
        border-radius: 50%; background: rgba(255,255,255,.05);
        pointer-events: none;
    }
    .page-hero h2 { font-size: 1.25rem; font-weight: 700; margin: 0 0 4px; letter-spacing: .2px; }
    .page-hero p  { margin: 0; opacity: .68; font-size: .82rem; }

    .hero-stat {
        background: rgba(255,255,255,.1); border-radius: 8px;
        padding: 9px 16px; text-align: center; min-width: 72px;
    }
    .hero-stat .num { font-size: 1.3rem; font-weight: 700; line-height: 1; }
    .hero-stat .lbl { font-size: .67rem; opacity: .7; margin-top: 2px; }

    /* Section cards */
    .section-card { border: none !important; border-radius: 10px !important; box-shadow: 0 1px 5px rgba(0,0,0,.08) !important; margin-bottom: 20px; overflow: hidden; }
    .section-card .card-header { background: #f8fafc !important; border-bottom: 2px solid #e2e8f0 !important; padding: 11px 20px; display: flex; align-items: center; gap: 8px; }
    .section-card .card-header .s-icon { width: 27px; height: 27px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; color: #fff; flex-shrink: 0; }
    .section-card .card-header h6 { margin: 0; font-weight: 700; font-size: .88rem; color: #1e293b; }
    .section-card > .card-body { padding: 20px; }

    /* Forms */
    .form-label { font-size: .79rem; font-weight: 600; color: #475569; margin-bottom: 4px; }
    .form-control, .form-select { border-radius: 8px !important; font-size: .875rem; border-color: #e2e8f0; padding: 8px 12px; }
    .form-control:focus, .form-select:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59,130,246,.12) !important; }
    .form-control[readonly] { background: #f8fafc; color: #94a3b8; }

    /* Table */
    .pa-table thead th { background: #1e293b; color: #fff; font-size: .76rem; font-weight: 600; letter-spacing: .35px; padding: 11px 16px; border: none; white-space: nowrap; }
    .pa-table tbody td { padding: 10px 16px; vertical-align: middle; font-size: .85rem; border-color: #f1f5f9; }
    .pa-table tbody tr:hover { background: #f8fafc; }

    /* Icon buttons */
    .btn-icon { border: none; border-radius: 6px; padding: 5px 10px; font-size: .76rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; transition: opacity .14s; }
    .btn-icon:hover { opacity: .8; }
    .btn-icon-blue   { background: #eff6ff; color: #1d4ed8; }
    .btn-icon-yellow { background: #fef9c3; color: #854d0e; }
    .btn-icon-green  { background: #dcfce7; color: #166534; }
    .btn-icon-red    { background: #fee2e2; color: #991b1b; }
    .btn-icon-gray   { background: #f1f5f9; color: #475569; }

    /* Badges */
    .pa-badge { display: inline-block; font-size: .71rem; font-weight: 600; padding: 3px 9px; border-radius: 5px; }
    .pa-badge-green  { background: #dcfce7; color: #166534; }
    .pa-badge-gray   { background: #f1f5f9; color: #64748b; }
    .pa-badge-red    { background: #fee2e2; color: #991b1b; }
    .pa-badge-blue   { background: #eff6ff; color: #1d4ed8; }
    .pa-badge-orange { background: #fff7ed; color: #c2410c; }
    .pa-badge-yellow { background: #fef9c3; color: #854d0e; }

    /* Hero ghost buttons */
    .btn-hero-ghost { background: rgba(255,255,255,.12); color: #fff !important; border: 1px solid rgba(255,255,255,.2); border-radius: 8px; font-size: .8rem; font-weight: 600; padding: 7px 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: background .14s; }
    .btn-hero-ghost:hover { background: rgba(255,255,255,.2); }

    /* Save button */
    .btn-save { background: linear-gradient(135deg, #1e293b, #334155); color: #fff !important; border: none; border-radius: 9px; padding: 11px 22px; font-weight: 700; font-size: .9rem; display: inline-flex; align-items: center; gap: 7px; transition: opacity .15s; cursor: pointer; }
    .btn-save:hover { opacity: .86; }

    /* Alerts */
    .alert { border-radius: 10px !important; font-size: .86rem; border: none !important; }
    .alert-success { background: #f0fdf4 !important; color: #166534 !important; }
    .alert-danger  { background: #fef2f2 !important; color: #991b1b !important; }
    .alert-warning { background: #fffbeb !important; color: #92400e !important; }
    .alert-info    { background: #eff6ff !important; color: #1e40af !important; }

    @media (min-width: 992px) { .sticky-side { position: sticky; top: 74px; } }

    /* ── Pagination (shared across all paginated list pages) ───────────── */
    .pa-pagination-wrap {
        background: #fff; border-radius: 10px;
        box-shadow: 0 1px 5px rgba(0,0,0,.08);
        padding: 12px 20px; margin-top: 16px;
    }
    .pa-pagination-wrap p { margin: 0; font-size: .82rem; }
    .pa-pagination-wrap .pagination { margin: 0; gap: 4px; }
    .pa-pagination-wrap .page-link {
        border: none; border-radius: 8px !important;
        color: #334155; font-size: .85rem; font-weight: 600;
        min-width: 38px; height: 38px;
        display: flex; align-items: center; justify-content: center;
        padding: 0 12px; margin: 0; box-shadow: none;
    }
    .pa-pagination-wrap .page-link:hover { background: #eff6ff; color: #1d4ed8; }
    .pa-pagination-wrap .page-item.active .page-link { background: #1e293b; color: #fff; }
    .pa-pagination-wrap .page-item.disabled .page-link { color: #cbd5e1; background: transparent; }
    </style>
</head>

<body>
<div class="wrapper">

    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="logo-row">
                <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <div class="logo-text">Peace Academy</div>
                    <div class="logo-sub">ERP System</div>
                </div>
            </div>
        </div>

        <div class="nav-section">Academic</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie nav-icon"></i> Dashboard
        </a>
        <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}">
            <i class="fas fa-user-graduate nav-icon"></i> Students
        </a>
        <a href="{{ route('enrollments.index') }}" class="{{ request()->routeIs('enrollments.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list nav-icon"></i> Enrollments
        </a>
        <a href="{{ route('classes.index') }}" class="{{ request()->routeIs('classes.*') ? 'active' : '' }}">
            <i class="fas fa-school nav-icon"></i> Classes
        </a>
        <a href="{{ route('sessions.index') }}" class="{{ request()->routeIs('sessions.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt nav-icon"></i> Sessions
        </a>
        <a href="{{ route('promotion.preview') }}" class="{{ request()->routeIs('promotion.*') ? 'active' : '' }}">
            <i class="fas fa-level-up-alt nav-icon"></i> Promotions
        </a>

        <div class="nav-section">Fee Management</div>
        <a href="{{ route('fee-vouchers.index') }}" class="{{ request()->routeIs('fee-vouchers.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar nav-icon"></i> Fee Vouchers
        </a>
        <a href="{{ route('monthly-fee-generator.create') }}" class="{{ request()->routeIs('monthly-fee-generator.*') ? 'active' : '' }}">
            <i class="fas fa-cogs nav-icon"></i> Monthly Fee Engine
        </a>
        <a href="{{ route('class-fee-structures.index') }}" class="{{ request()->routeIs('class-fee-structures.*') ? 'active' : '' }}">
            <i class="fas fa-layer-group nav-icon"></i> Fee Structures
        </a>

        <div class="nav-section">Accounts</div>
        <a href="{{ route('student-ledger.index') }}" class="{{ request()->routeIs('student-ledger.*') ? 'active' : '' }}">
            <i class="fas fa-book-open nav-icon"></i> Student Ledger
        </a>
        <a href="{{ route('fee-payments.index') }}" class="{{ request()->routeIs('fee-payments.*') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave nav-icon"></i> Payment History
        </a>
        <a href="{{ route('previous-balances.index') }}" class="{{ request()->routeIs('previous-balances.*') ? 'active' : '' }}">
            <i class="fas fa-exclamation-circle nav-icon"></i> Previous Balances
        </a>
        <a href="{{ route('monthly-ledger.index') }}" class="{{ request()->routeIs('monthly-ledger.*') ? 'active' : '' }}">
            <i class="fas fa-book nav-icon"></i> Monthly Ledger
        </a>
        <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i class="fas fa-receipt nav-icon"></i> Expenses
        </a>
    </aside>

    <div class="main">
        <div class="topbar">
            <span class="topbar-left">
                <i class="fas fa-circle text-success me-2" style="font-size:.42rem;vertical-align:middle;"></i>
                Peace Academy ERP
            </span>
            <div class="topbar-right">
                <span>
                    <i class="fas fa-user-circle me-1"></i>
                    {{ auth()->check() ? auth()->user()->name : 'Guest' }}
                    @if(auth()->check() && auth()->user()->role)
                        <span class="ms-1 pa-badge pa-badge-blue" style="font-size:.62rem;">{{ auth()->user()->role }}</span>
                    @endif
                </span>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit"
                            style="background:none;border:none;padding:0;cursor:pointer;color:#64748b;font-size:.76rem;">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="content-area">
            @yield('content')
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
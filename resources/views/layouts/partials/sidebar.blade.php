<div class="sidebar">

    <h2>Peace Academy</h2>

    {{-- Academic Management --}}
    <div class="menu-title" style="color:#94a3b8; font-size:11px; text-transform:uppercase;
         letter-spacing:1px; padding:12px 15px 4px; font-weight:bold;">
        Academic
    </div>

    <a href="{{ route('dashboard') }}"
       class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
    </a>

    <a href="{{ route('students.index') }}"
       class="{{ request()->routeIs('students.*') ? 'active' : '' }}">
        <i class="fas fa-user-graduate me-2"></i> Students
    </a>

    <a href="{{ route('enrollments.index') }}"
       class="{{ request()->routeIs('enrollments.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list me-2"></i> Enrollments
    </a>

    <a href="{{ route('classes.index') }}"
       class="{{ request()->routeIs('classes.*') ? 'active' : '' }}">
        <i class="fas fa-school me-2"></i> Classes
    </a>

    <a href="{{ route('sessions.index') }}"
       class="{{ request()->routeIs('sessions.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt me-2"></i> Sessions
    </a>

    <a href="{{ route('promotion.preview') }}"
       class="{{ request()->routeIs('promotion.*') ? 'active' : '' }}">
        <i class="fas fa-level-up-alt me-2"></i> Promotions
    </a>

    {{-- Fee Management --}}
    <div class="menu-title" style="color:#94a3b8; font-size:11px; text-transform:uppercase;
         letter-spacing:1px; padding:12px 15px 4px; font-weight:bold;">
        Fee Management
    </div>

    <a href="{{ route('fee-vouchers.index') }}"
       class="{{ request()->routeIs('fee-vouchers.*') ? 'active' : '' }}">
        <i class="fas fa-file-invoice-dollar me-2"></i> Fee Vouchers
    </a>

    <a href="{{ route('fee-matrix.index') }}"
       class="{{ request()->routeIs('fee-matrix.*') ? 'active' : '' }}">
        <i class="fas fa-table me-2"></i> Fee Matrix
    </a>

    <a href="{{ route('monthly-fee-generator.create') }}"
       class="{{ request()->routeIs('monthly-fee-generator.*') ? 'active' : '' }}">
        <i class="fas fa-cogs me-2"></i> Monthly Fee Engine
    </a>

    <a href="{{ route('class-fee-structures.index') }}"
       class="{{ request()->routeIs('class-fee-structures.*') ? 'active' : '' }}">
        <i class="fas fa-layer-group me-2"></i> Fee Structures
    </a>

    {{-- Accounts & Ledger --}}
    <div class="menu-title" style="color:#94a3b8; font-size:11px; text-transform:uppercase;
         letter-spacing:1px; padding:12px 15px 4px; font-weight:bold;">
        Accounts
    </div>

    <a href="{{ route('student-ledger.index') }}"
       class="{{ request()->routeIs('student-ledger.*') ? 'active' : '' }}">
        <i class="fas fa-book-open me-2"></i> Student Ledger
    </a>

    <a href="{{ route('previous-balances.index') }}"
       class="{{ request()->routeIs('previous-balances.*') ? 'active' : '' }}">
        <i class="fas fa-exclamation-circle me-2"></i> Previous Balances
    </a>

    <a href="{{ route('monthly-ledger.index') }}"
       class="{{ request()->routeIs('monthly-ledger.*') ? 'active' : '' }}">
        <i class="fas fa-balance-scale me-2"></i> Monthly Ledger
    </a>

    <a href="{{ route('expenses.index') }}"
       class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
        <i class="fas fa-receipt me-2"></i> Expenses
    </a>

    {{-- Settings --}}
    <div class="menu-title" style="color:#94a3b8; font-size:11px; text-transform:uppercase;
         letter-spacing:1px; padding:12px 15px 4px; font-weight:bold;">
        Settings
    </div>

    <a href="{{ route('settings.payment-methods.index') }}"
       class="{{ request()->routeIs('settings.payment-methods.*') ? 'active' : '' }}">
        <i class="fas fa-sliders-h me-2"></i> Payment Methods
    </a>

</div>

<style>
    .sidebar .menu-title { display: block; }
    .sidebar a.active {
        background: #334155;
        border-left: 3px solid #38bdf8;
        padding-left: 12px;
    }
</style>
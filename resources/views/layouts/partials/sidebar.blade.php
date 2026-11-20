
<div class="sidebar">

    <h2 class="sidebar-heading">Peace Academy</h2>

    <div class="sidebar-menu">

        {{-- Academic Management --}}
        <div class="menu-title">Academic</div>

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
        <div class="menu-title">Fee Management</div>

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
        <div class="menu-title">Accounts</div>

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
        <div class="menu-title">Settings</div>

        <a href="{{ route('settings.payment-methods.index') }}"
           class="{{ request()->routeIs('settings.payment-methods.*') ? 'active' : '' }}">
            <i class="fas fa-sliders-h me-2"></i> Payment Methods
        </a>

    </div>

</div>


<style>

/* Main Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    height: 100dvh;
    background: #1e293b;
    color: white;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 1000;
}

/* Sidebar Heading */
.sidebar-heading {
    flex-shrink: 0;
    margin: 0;
    padding: 20px 15px;
    text-align: center;
    font-size: 21px;
    color: white;
    border-bottom: 1px solid #334155;
}

/* Scrollable Menu */
.sidebar-menu {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 8px 0 25px;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}

/* Menu Titles */
.sidebar .menu-title {
    display: block;
    color: #94a3b8;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 15px 15px 6px;
    font-weight: bold;
}

/* Menu Links */
.sidebar-menu a {
    display: block;
    color: #cbd5e1;
    padding: 11px 15px;
    text-decoration: none;
    font-size: 14px;
    transition: background 0.2s;
}

/* Hover */
.sidebar-menu a:hover {
    background: #334155;
    color: white;
}

/* Active Link */
.sidebar-menu a.active {
    background: #334155;
    border-left: 3px solid #38bdf8;
    padding-left: 12px;
    color: white;
}

/* Scrollbar */
.sidebar-menu::-webkit-scrollbar {
    width: 6px;
}

.sidebar-menu::-webkit-scrollbar-track {
    background: #1e293b;
}

.sidebar-menu::-webkit-scrollbar-thumb {
    background: #64748b;
    border-radius: 10px;
}

.sidebar-menu::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

</style>
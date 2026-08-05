@extends('layouts.dashboard')

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     STYLES
═══════════════════════════════════════════════════════════ --}}
<style>
:root {
    --pa-dark:   #1e293b;
    --pa-darker: #0f172a;
    --pa-blue:   #3b82f6;
    --pa-green:  #10b981;
    --pa-red:    #ef4444;
    --pa-amber:  #f59e0b;
    --pa-purple: #8b5cf6;
    --pa-teal:   #14b8a6;
    --pa-sky:    #0ea5e9;
    --pa-pink:   #ec4899;
    --pa-bg:     #f1f5f9;
    --pa-border: #e2e8f0;
    --pa-muted:  #64748b;
}

body { background: var(--pa-bg); }

/* ── Hero ── */
.dash-hero {
    background: linear-gradient(135deg, var(--pa-darker) 0%, var(--pa-dark) 55%, #0c4a6e 100%);
    border-radius: 14px;
    padding: 28px 32px 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 0;
}
.dash-hero::after {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    pointer-events: none;
}
.dash-hero .hero-label { font-size: .7rem; letter-spacing: .12em; text-transform: uppercase; color: #94a3b8; }
.dash-hero h1 { font-size: 1.5rem; font-weight: 800; margin: 4px 0 2px; }
.dash-hero .hero-sub { font-size: .82rem; color: #38bdf8; }
.dash-hero .hero-meta { font-size: .78rem; color: #cbd5e1; }
.hero-clock { font-size: .85rem; font-weight: 600; color: #e2e8f0; }

/* ── Quick actions ── */
.qa-strip { display: flex; gap: .6rem; flex-wrap: wrap; margin: 18px 0 4px; }
.qa-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    background: #fff; border: 1px solid var(--pa-border);
    border-radius: 9px; padding: .45rem .9rem;
    font-size: .77rem; font-weight: 600; color: var(--pa-dark);
    text-decoration: none; transition: background .14s, box-shadow .14s;
    white-space: nowrap;
}
.qa-btn:hover { background: #f8fafc; box-shadow: 0 2px 8px rgba(0,0,0,.08); color: var(--pa-dark); }
.qa-btn i { font-size: .82rem; }

/* ── Metric tiles ── */
.metric-tile {
    background: #fff;
    border-radius: 13px;
    padding: 18px 20px 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    border-left: 4px solid transparent;
    transition: transform .17s, box-shadow .17s;
    text-decoration: none !important;
    display: block;
    height: 100%;
    color: inherit;
}
.metric-tile:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,.1); color: inherit; }
.metric-tile.no-link:hover { transform: none; box-shadow: 0 2px 12px rgba(0,0,0,.06); cursor: default; }

.mt-blue   { border-color: var(--pa-blue); }
.mt-green  { border-color: var(--pa-green); }
.mt-red    { border-color: var(--pa-red); }
.mt-amber  { border-color: var(--pa-amber); }
.mt-purple { border-color: var(--pa-purple); }
.mt-teal   { border-color: var(--pa-teal); }
.mt-sky    { border-color: var(--pa-sky); }
.mt-pink   { border-color: var(--pa-pink); }

.tile-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; margin-bottom: 12px; flex-shrink: 0;
}
.ic-blue   { background: #eff6ff; color: var(--pa-blue); }
.ic-green  { background: #f0fdf4; color: var(--pa-green); }
.ic-red    { background: #fef2f2; color: var(--pa-red); }
.ic-amber  { background: #fffbeb; color: var(--pa-amber); }
.ic-purple { background: #f5f3ff; color: var(--pa-purple); }
.ic-teal   { background: #f0fdfa; color: var(--pa-teal); }
.ic-sky    { background: #f0f9ff; color: var(--pa-sky); }
.ic-pink   { background: #fdf2f8; color: var(--pa-pink); }

.tile-value { font-size: 1.6rem; font-weight: 800; color: var(--pa-darker); line-height: 1; }
.tile-label { font-size: .68rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .07em; margin-top: 5px; }
.tile-sub   { font-size: .74rem; color: var(--pa-muted); margin-top: 6px; }

.tile-badge {
    display: inline-block; font-size: .67rem; font-weight: 700;
    padding: .18em .6em; border-radius: 20px; margin-top: 7px;
}
.tb-green  { background: #dcfce7; color: #15803d; }
.tb-red    { background: #fee2e2; color: #dc2626; }
.tb-amber  { background: #fef9c3; color: #92400e; }
.tb-blue   { background: #dbeafe; color: #1d4ed8; }
.tb-muted  { background: #f1f5f9; color: #64748b; }

/* ── Section headings ── */
.sec-head {
    display: flex; align-items: center; justify-content: space-between;
    margin: 28px 0 14px;
}
.sec-head h6 {
    margin: 0; font-size: .75rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .09em; color: var(--pa-dark);
    display: flex; align-items: center; gap: 7px;
}
.sec-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

/* ── Chart cards ── */
.chart-card {
    background: #fff;
    border-radius: 13px;
    padding: 20px 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    height: 100%;
}
.cc-title { font-size: .78rem; font-weight: 800; color: var(--pa-dark); text-transform: uppercase; letter-spacing: .06em; }
.cc-sub   { font-size: .71rem; color: #94a3b8; margin-bottom: 16px; }

/* ── Financial table ── */
.fin-table { width: 100%; border-collapse: collapse; font-size: .81rem; }
.fin-table thead th {
    background: var(--pa-dark); color: #94a3b8;
    font-size: .67rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; padding: 9px 12px; border: none;
    white-space: nowrap;
}
.fin-table thead th:first-child { border-radius: 0; }
.fin-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.fin-table tbody tr:last-child td { border-bottom: none; }
.fin-table tbody tr:hover td { background: #f8fafc; }
.fin-table tfoot td { padding: 9px 12px; font-weight: 700; background: #f8fafc; font-size: .8rem; }
.amt { font-family: 'Courier New', monospace; font-weight: 700; }
.amt-green { color: var(--pa-green); }
.amt-red   { color: var(--pa-red); }
.amt-blue  { color: var(--pa-blue); }
.amt-amber { color: var(--pa-amber); }

/* ── Defaulters ── */
.def-row {
    display: flex; align-items: center; gap: 12px;
    padding: 9px 0; border-bottom: 1px solid #f1f5f9;
}
.def-row:last-child { border-bottom: none; }
.def-av {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--pa-dark), var(--pa-blue));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 800;
}
.def-name  { font-size: .82rem; font-weight: 700; color: var(--pa-darker); }
.def-class { font-size: .71rem; color: #94a3b8; }
.def-amt   { font-size: .82rem; font-weight: 800; color: var(--pa-red); margin-left: auto; font-family: 'Courier New', monospace; white-space: nowrap; }

/* ── Progress bars ── */
.pb-wrap { margin-bottom: 11px; }
.pb-label { display: flex; justify-content: space-between; font-size: .74rem; color: var(--pa-muted); margin-bottom: 4px; font-weight: 500; }
.pb-track { height: 7px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
.pb-fill  { height: 100%; border-radius: 4px; transition: width 1.2s ease; }

/* ── Annual summary blocks ── */
.ann-block {
    border-left: 3px solid transparent; padding-left: 12px; margin-bottom: 14px;
}
.ann-block .ann-label { font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; }
.ann-block .ann-val   { font-size: 1.35rem; font-weight: 800; line-height: 1.15; margin-top: 1px; }

/* ── Action items ── */
.action-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 0; border-bottom: 1px solid #f8fafc; font-size: .79rem;
}
.action-row:last-child { border-bottom: none; }
.action-row .ar-label { color: var(--pa-dark); display: flex; align-items: center; gap: 7px; }
.action-row .ar-label i { font-size: .45rem; }

/* ── Responsive ── */
@media (max-width: 767px) {
    .dash-hero { padding: 20px 18px 24px; }
    .tile-value { font-size: 1.3rem; }
}
</style>

{{-- ═══════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════ --}}
<div class="dash-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div class="hero-label">Peace Academy ERP</div>
            <h1><i class="fas fa-chart-line me-2" style="color:#38bdf8"></i>Finance Dashboard</h1>
            <div class="hero-sub">Real-time academic &amp; financial command centre</div>
        </div>
        <div class="text-md-end">
            <div class="hero-meta"><i class="fas fa-calendar me-1"></i>{{ now()->format('l, d M Y') }}</div>
            <div class="hero-clock mt-1" id="live-clock"></div>
            <div class="mt-1">
                <span style="background:rgba(56,189,248,.15);color:#38bdf8;font-size:.72rem;font-weight:700;padding:.2em .7em;border-radius:20px">
                    <i class="fas fa-graduation-cap me-1"></i>FY {{ now()->year }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ── Quick Actions ── --}}
<div class="qa-strip">
    <a href="{{ url('/fee-vouchers/create') }}" class="qa-btn"><i class="fas fa-plus-circle" style="color:#10b981"></i> New Voucher</a>
    <a href="{{ url('/students/create') }}"     class="qa-btn"><i class="fas fa-user-plus"    style="color:#3b82f6"></i> Enroll Student</a>
    <a href="{{ url('/student-ledger') }}"      class="qa-btn"><i class="fas fa-book-open"   style="color:#0ea5e9"></i> Ledger</a>
    <a href="{{ url('/fee-vouchers?status=unpaid') }}" class="qa-btn"><i class="fas fa-exclamation-circle" style="color:#ef4444"></i> Unpaid Vouchers</a>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ROW 1 — Primary KPI Tiles
═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mt-1">

    {{-- Total Students --}}
<div class="col-6 col-md-3">
        <a href="{{ url('/students') }}" class="metric-tile mt-blue">
            <div class="tile-icon ic-blue"><i class="fas fa-users"></i></div>
            <div class="tile-value">{{ number_format($activeStudents) }}</div>
            <div class="tile-label">Active Students</div>
            <div class="tile-sub"><i class="fas fa-circle text-muted me-1" style="font-size:.45rem"></i>{{ number_format($totalStudents) }} total</div>
            <span class="tile-badge tb-blue"><i class="fas fa-arrow-right me-1"></i>View all</span>
        </a>
    </div>

    {{-- Total Classes --}}
    <div class="col-6 col-md-3">
        <a href="{{ url('/classes') }}" class="metric-tile mt-purple">
            <div class="tile-icon ic-purple"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="tile-value">{{ number_format($totalClasses) }}</div>
            <div class="tile-label">Total Classes</div>
            <div class="tile-sub">Active classes this session</div>
            <span class="tile-badge tb-blue"><i class="fas fa-arrow-right me-1"></i>View all</span>
        </a>
    </div>

    {{-- Total Vouchers --}}
    <div class="col-6 col-md-3">
        <a href="{{ url('/fee-vouchers') }}" class="metric-tile mt-teal">
            <div class="tile-icon ic-teal"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="tile-value">{{ number_format($totalVouchers) }}</div>
            <div class="tile-label">Total Vouchers</div>
            <div class="tile-sub">
                <span style="color:#10b981;font-weight:700">{{ $paidVouchers }} paid</span>
                &nbsp;·&nbsp;
                <span style="color:#f59e0b;font-weight:700">{{ $partialVouchers }} partial</span>
            </div>
            <span class="tile-badge tb-blue"><i class="fas fa-arrow-right me-1"></i>View all</span>
        </a>
    </div>

    {{-- Unpaid Vouchers --}}
    <div class="col-6 col-md-3">
        <a href="{{ url('/fee-vouchers?status=unpaid') }}" class="metric-tile mt-red">
            <div class="tile-icon ic-red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="tile-value">{{ number_format($unpaidVouchers) }}</div>
            <div class="tile-label">Unpaid Vouchers</div>
            <div class="tile-sub">Requires follow-up</div>
            <span class="tile-badge tb-red"><i class="fas fa-clock me-1"></i>{{ $overdueVouchers }} overdue</span>
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ROW 2 — Financial KPI Tiles
═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mt-0">

    {{-- Total Fee Generated --}}
    <div class="col-6 col-md-3">
        <div class="metric-tile mt-sky no-link">
            <div class="tile-icon ic-sky"><i class="fas fa-file-invoice"></i></div>
            <div class="tile-value" style="font-size:1.25rem">Rs {{ number_format($totalFeeGenerated) }}</div>
            <div class="tile-label">Total Fee Billed</div>
            <div class="tile-sub">All-time across all vouchers</div>
            <span class="tile-badge tb-muted">All time</span>
        </div>
    </div>

    {{-- Total Collected --}}
    <div class="col-6 col-md-3">
        <div class="metric-tile mt-green no-link">
            <div class="tile-icon ic-green"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="tile-value" style="font-size:1.25rem">Rs {{ number_format($totalCollected) }}</div>
            <div class="tile-label">Total Collected</div>
            <div class="tile-sub">All payments received</div>
            <span class="tile-badge tb-green"><i class="fas fa-check-circle me-1"></i>All time</span>
        </div>
    </div>

    {{-- Total Outstanding --}}
    <div class="col-6 col-md-3">
        <div class="metric-tile mt-amber no-link">
            <div class="tile-icon ic-amber"><i class="fas fa-balance-scale"></i></div>
            <div class="tile-value" style="font-size:1.25rem">Rs {{ number_format($totalOutstanding) }}</div>
            <div class="tile-label">Total Outstanding</div>
            <div class="tile-sub">Across all unpaid vouchers</div>
            <span class="tile-badge tb-amber"><i class="fas fa-warning me-1"></i>Needs recovery</span>
        </div>
    </div>

    {{-- Current Month Outstanding --}}
    <div class="col-6 col-md-3">
        <div class="metric-tile mt-pink no-link">
            <div class="tile-icon ic-pink"><i class="fas fa-calendar-minus"></i></div>
            <div class="tile-value" style="font-size:1.25rem">Rs {{ number_format($currentMonthOutstanding) }}</div>
            <div class="tile-label">This Month Outstanding</div>
            <div class="tile-sub">{{ now()->format('F Y') }}</div>
            @if($collectionRate >= 80)
                <span class="tile-badge tb-green">{{ $collectionRate }}% collected</span>
            @elseif($collectionRate >= 50)
                <span class="tile-badge tb-amber">{{ $collectionRate }}% collected</span>
            @else
                <span class="tile-badge tb-red">{{ $collectionRate }}% collected</span>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ROW 3 — Today & Month Collection
═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mt-0">

    <div class="col-6 col-md-3">
        <div class="metric-tile mt-green no-link">
            <div class="tile-icon ic-green"><i class="fas fa-coins"></i></div>
            <div class="tile-value" style="font-size:1.25rem">Rs {{ number_format($todayCollection) }}</div>
            <div class="tile-label">Today's Collection</div>
            <div class="tile-sub">{{ now()->format('d M Y') }}</div>
            <span class="tile-badge tb-green"><i class="fas fa-calendar-day me-1"></i>Live</span>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-tile mt-blue no-link">
            <div class="tile-icon ic-blue"><i class="fas fa-wallet"></i></div>
            <div class="tile-value" style="font-size:1.25rem">Rs {{ number_format($monthCollection) }}</div>
            <div class="tile-label">This Month Collected</div>
            <div class="tile-sub">{{ now()->format('F Y') }}</div>
            <span class="tile-badge tb-blue">vs Rs {{ number_format($currentMonthBilled) }} billed</span>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-tile mt-purple no-link">
            <div class="tile-icon ic-purple"><i class="fas fa-percentage"></i></div>
            <div class="tile-value">{{ $annualCollectionRate }}%</div>
            <div class="tile-label">Annual Recovery Rate</div>
            <div class="tile-sub">Fee collection efficiency YTD</div>
            @if($annualCollectionRate >= 80)
                <span class="tile-badge tb-green">On target</span>
            @elseif($annualCollectionRate >= 60)
                <span class="tile-badge tb-amber">Needs attention</span>
            @else
                <span class="tile-badge tb-red">Below target</span>
            @endif
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-tile mt-amber no-link">
            <div class="tile-icon ic-amber"><i class="fas fa-hourglass-half"></i></div>
            <div class="tile-value">{{ $dueThisWeek }}</div>
            <div class="tile-label">Due This Week</div>
            <div class="tile-sub">Vouchers expiring in 7 days</div>
            <span class="tile-badge {{ $dueThisWeek > 0 ? 'tb-amber' : 'tb-green' }}">
                <i class="fas fa-bell me-1"></i>{{ $dueThisWeek > 0 ? 'Action needed' : 'Clear' }}
            </span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     CHARTS
═══════════════════════════════════════════════════════════ --}}
<div class="sec-head">
    <h6><span class="sec-dot bg-primary"></span>Monthly Financial Performance</h6>
    <small class="text-muted" style="font-size:.73rem">12-month rolling</small>
</div>

<div class="row g-3">

    {{-- Main bar chart --}}
    <div class="col-md-8">
        <div class="chart-card">
            <div class="cc-title">Monthly Billing vs Collection</div>
            <div class="cc-sub">Billed amount vs cash received — last 12 months</div>
            <canvas id="barChart" height="105"></canvas>
        </div>
    </div>

    {{-- Donut --}}
    <div class="col-md-4">
        <div class="chart-card">
            <div class="cc-title">Voucher Status Split</div>
            <div class="cc-sub">Current distribution</div>
            <canvas id="donutChart" height="165"></canvas>
            <div id="donut-legend" class="mt-2"></div>
        </div>
    </div>
</div>

<div class="row g-3 mt-0">

    {{-- Recovery rate line --}}
    <div class="col-md-5">
        <div class="chart-card">
            <div class="cc-title">Monthly Recovery Rate %</div>
            <div class="cc-sub">Percentage of billed fees collected each month</div>
            <canvas id="lineChart" height="135"></canvas>
        </div>
    </div>

    {{-- Action items --}}
    <div class="col-md-7">
        <div class="chart-card">
            <div class="cc-title">📋 Action Items</div>
            <div class="cc-sub">Requires attention now</div>

            <div class="action-row">
                <div class="ar-label"><i class="fas fa-circle" style="color:#ef4444"></i>Overdue vouchers (past due date)</div>
                <span class="badge" style="background:#fee2e2;color:#dc2626;font-size:.72rem">{{ $overdueVouchers }}</span>
            </div>
            <div class="action-row">
                <div class="ar-label"><i class="fas fa-circle" style="color:#f59e0b"></i>Due within 7 days</div>
                <span class="badge" style="background:#fef9c3;color:#92400e;font-size:.72rem">{{ $dueThisWeek }}</span>
            </div>
            <div class="action-row">
                <div class="ar-label"><i class="fas fa-circle" style="color:#8b5cf6"></i>Partial payments pending</div>
                <span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:.72rem">{{ $partialVouchers }}</span>
            </div>
            <div class="action-row">
                <div class="ar-label"><i class="fas fa-circle" style="color:#10b981"></i>Paid this week</div>
                <span class="badge" style="background:#dcfce7;color:#15803d;font-size:.72rem">{{ $paidThisWeek }}</span>
            </div>
            <div class="action-row">
                <div class="ar-label"><i class="fas fa-circle" style="color:#0ea5e9"></i>Total unpaid amount</div>
                <span class="badge" style="background:#f0f9ff;color:#0369a1;font-size:.72rem">Rs {{ number_format($totalOutstanding) }}</span>
            </div>
            <div class="action-row">
                <div class="ar-label"><i class="fas fa-circle" style="color:#3b82f6"></i>This month billed</div>
                <span class="badge" style="background:#dbeafe;color:#1d4ed8;font-size:.72rem">Rs {{ number_format($currentMonthBilled) }}</span>
            </div>

            <div class="d-flex gap-2 mt-3 flex-wrap">
                <a href="{{ url('/fee-vouchers/create') }}" class="btn btn-sm btn-primary" style="border-radius:8px;font-size:.75rem;flex:1">
                    <i class="fas fa-plus me-1"></i>Generate Voucher
                </a>
                <a href="{{ url('/fee-vouchers?status=unpaid') }}" class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:.75rem;flex:1">
                    <i class="fas fa-list me-1"></i>View Defaulters
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     MONTHLY SUMMARY TABLE + DEFAULTERS
═══════════════════════════════════════════════════════════ --}}
<div class="sec-head">
    <h6><span class="sec-dot bg-danger"></span>Monthly Summary &amp; Top Defaulters</h6>
</div>

<div class="row g-3">

    {{-- Monthly table --}}
    <div class="col-lg-7">
        <div class="chart-card p-0" style="overflow:hidden">
            <div style="padding:16px 20px 8px">
                <div class="cc-title">Monthly Fee Summary</div>
                <div class="cc-sub">Last 6 months — billed, collected &amp; outstanding</div>
            </div>
            <div style="overflow-x:auto">
                <table class="fin-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Vouchers</th>
                            <th class="text-end">Billed (Rs)</th>
                            <th class="text-end">Collected (Rs)</th>
                            <th class="text-end">Outstanding (Rs)</th>
                            <th class="text-center">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlySummary as $row)
                        <tr>
                            <td style="font-weight:600">{{ $row['label'] }}</td>
                            <td class="text-end amt">{{ $row['voucher_count'] }}</td>
                            <td class="text-end amt amt-blue">{{ number_format($row['billed']) }}</td>
                            <td class="text-end amt amt-green">{{ number_format($row['collected']) }}</td>
                            <td class="text-end amt amt-red">{{ number_format($row['outstanding']) }}</td>
                            <td class="text-center">
                                @php $r = $row['rate']; @endphp
                                <span class="badge" style="font-size:.67rem;background:{{ $r>=80?'#dcfce7;color:#15803d':($r>=50?'#fef9c3;color:#92400e':'#fee2e2;color:#dc2626') }}">
                                    {{ $r }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4" style="font-size:.8rem">No data yet</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="font-weight:800">6-Month Total</td>
                            <td class="text-end amt">{{ collect($monthlySummary)->sum('voucher_count') }}</td>
                            <td class="text-end amt amt-blue">{{ number_format(collect($monthlySummary)->sum('billed')) }}</td>
                            <td class="text-end amt amt-green">{{ number_format(collect($monthlySummary)->sum('collected')) }}</td>
                            <td class="text-end amt amt-red">{{ number_format(collect($monthlySummary)->sum('outstanding')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Defaulters + Annual --}}
    <div class="col-lg-5 d-flex flex-column gap-3">

        {{-- Top Defaulters --}}
        <div class="chart-card">
            <div class="cc-title">⚠️ Top Defaulters</div>
            <div class="cc-sub">Highest outstanding balances</div>
            @forelse($topDefaulters as $d)
            <div class="def-row">
                <div class="def-av">{{ strtoupper(mb_substr($d['name'] ?? 'NA', 0, 2)) }}</div>
                <div style="min-width:0">
                    <div class="def-name text-truncate">{{ $d['name'] }}</div>
                    <div class="def-class">{{ $d['class'] }} · {{ $d['months'] }} voucher(s)</div>
                </div>
                <div class="def-amt">Rs {{ number_format($d['outstanding']) }}</div>
            </div>
            @empty
            <div class="text-center text-muted py-3" style="font-size:.8rem">
                <i class="fas fa-check-circle text-success me-1"></i>No unpaid vouchers — great!
            </div>
            @endforelse
            <a href="{{ url('/fee-vouchers?status=unpaid') }}" class="btn btn-sm btn-outline-danger w-100 mt-3" style="border-radius:8px;font-size:.75rem">
                <i class="fas fa-list me-1"></i>View All Unpaid
            </a>
        </div>

        {{-- Annual Snapshot --}}
        <div class="chart-card">
            <div class="cc-title">Annual Snapshot — {{ now()->year }}</div>
            <div class="cc-sub">Year-to-date financial position</div>

            <div class="ann-block" style="border-color:var(--pa-blue)">
                <div class="ann-label">Total Billed</div>
                <div class="ann-val amt-blue">Rs {{ number_format($annualBilled) }}</div>
            </div>
            <div class="ann-block" style="border-color:var(--pa-green)">
                <div class="ann-label">Total Collected</div>
                <div class="ann-val amt-green">Rs {{ number_format($annualCollected) }}</div>
            </div>
            <div class="ann-block" style="border-color:var(--pa-red)">
                <div class="ann-label">Total Outstanding</div>
                <div class="ann-val amt-red">Rs {{ number_format($annualOutstanding) }}</div>
            </div>
            <div class="ann-block" style="border-color:var(--pa-amber)">
                <div class="ann-label">Recovery Rate</div>
                <div class="ann-val" style="color:var(--pa-amber)">{{ $annualCollectionRate }}%</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     RECENT VOUCHERS
═══════════════════════════════════════════════════════════ --}}
<div class="sec-head">
    <h6><span class="sec-dot bg-success"></span>Recent Vouchers</h6>
    <a href="{{ url('/fee-vouchers') }}" style="font-size:.75rem;color:var(--pa-blue);font-weight:600;text-decoration:none">
        View all <i class="fas fa-arrow-right ms-1"></i>
    </a>
</div>

<div class="chart-card p-0 mb-4" style="overflow:hidden">
    <div style="overflow-x:auto">
        <table class="fin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Voucher No.</th>
                    <th class="text-end">Payable (Rs)</th>
                    <th class="text-end">Balance (Rs)</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentVouchers as $v)
                <tr>
                    <td style="color:#94a3b8;font-size:.75rem">{{ $loop->iteration }}</td>
                    <td>
                        <span style="font-weight:600;color:var(--pa-darker)">
                            {{ optional($v->student)->student_name ?? optional($v->student)->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:.78rem;background:#f1f5f9;padding:2px 7px;border-radius:4px;color:#1d4ed8;font-weight:600">
                            {{ $v->voucher_no ?? $v->id }}
                        </span>
                    </td>
                    <td class="text-end amt amt-blue">{{ number_format($v->payable_amount) }}</td>
                    <td class="text-end amt amt-red">{{ number_format($v->balance_amount) }}</td>
                    <td class="text-center">
                        @php
                            $s = strtolower($v->status ?? '');
                            $sc = match($s) {
                                'paid'    => 'background:#dcfce7;color:#15803d',
                                'unpaid'  => 'background:#fee2e2;color:#dc2626',
                                'partial' => 'background:#fef9c3;color:#92400e',
                                default   => 'background:#f1f5f9;color:#64748b',
                            };
                        @endphp
                        <span class="badge" style="{{ $sc }};font-size:.67rem;font-weight:700">
                            {{ ucfirst($v->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ url('/fee-vouchers/' . $v->id) }}" class="btn btn-sm" style="border-radius:6px;font-size:.72rem;font-weight:600;background:#eff6ff;color:#1d4ed8;border:none;padding:4px 10px">
                            <i class="fas fa-eye me-1"></i>View
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4" style="font-size:.8rem">No vouchers found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Live clock
(function tick(){
    const el = document.getElementById('live-clock');
    if(el) el.textContent = new Date().toLocaleTimeString('en-PK',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    setTimeout(tick,1000);
})();

// Data from controller
const labels    = {!! json_encode($chartLabels) !!};
const billed    = {!! json_encode($chartBilled) !!};
const collected = {!! json_encode($chartCollected) !!};
const rates     = {!! json_encode($chartRates) !!};

const paid    = {{ $paidVouchers }};
const unpaid  = {{ $unpaidVouchers }};
const partial = {{ $partialVouchers }};

Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#64748b';

// 1. Monthly Bar
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Billed',
                data: billed,
                backgroundColor: 'rgba(59,130,246,.18)',
                borderColor: '#3b82f6',
                borderWidth: 1.5,
                borderRadius: 5,
            },
            {
                label: 'Collected',
                data: collected,
                backgroundColor: 'rgba(16,185,129,.22)',
                borderColor: '#10b981',
                borderWidth: 1.5,
                borderRadius: 5,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 14 }},
            tooltip: { callbacks: { label: c => ' Rs ' + c.parsed.y.toLocaleString() }}
        },
        scales: {
            y: { grid: { color: '#f1f5f9' }, ticks: { callback: v => 'Rs ' + (v >= 1000 ? Math.round(v/1000)+'k' : v) }},
            x: { grid: { display: false }}
        }
    }
});

// 2. Donut
const donutColors  = ['#10b981','#ef4444','#f59e0b'];
const donutLabels  = ['Paid','Unpaid','Partial'];
const donutData    = [paid, unpaid, partial];

new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: donutLabels,
        datasets: [{ data: donutData, backgroundColor: donutColors, borderWidth: 0, hoverOffset: 6 }]
    },
    options: {
        responsive: true, cutout: '72%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed}` }}
        }
    }
});

// Manual donut legend
const leg = document.getElementById('donut-legend');
donutLabels.forEach((l,i) => {
    leg.innerHTML += `<span style="display:inline-flex;align-items:center;gap:.3rem;margin-right:.65rem;font-size:.71rem;color:#334155">
        <span style="width:9px;height:9px;border-radius:50%;background:${donutColors[i]};display:inline-block"></span>
        ${l}: <strong>${donutData[i]}</strong></span>`;
});

// 3. Recovery Rate Line
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Recovery %',
            data: rates,
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139,92,246,.1)',
            fill: true, tension: .4,
            pointBackgroundColor: '#8b5cf6', pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c => ` ${c.parsed.y}%` }}
        },
        scales: {
            y: {
                min: 0, max: 100,
                grid: { color: '#f1f5f9' },
                ticks: { callback: v => v + '%' }
            },
            x: { grid: { display: false }}
        }
    }
});
</script>

@endsection
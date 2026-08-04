@extends('layouts.dashboard')

@section('content')

<style>
.fm-wrap { overflow-x: auto; border-radius: 10px; }
.fm-table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: .8rem; white-space: nowrap; }
.fm-table th, .fm-table td { padding: 8px 10px; border-bottom: 1px solid #eef1f5; }
.fm-table thead th {
    background: #1e293b; color: #fff; position: sticky; top: 0; z-index: 3;
    font-size: .72rem; text-transform: uppercase; letter-spacing: .4px;
}
.fm-sticky-1 { position: sticky; left: 0; z-index: 2; background: #fff; min-width: 90px; }
.fm-sticky-2 { position: sticky; left: 90px; z-index: 2; background: #fff; min-width: 170px; }
.fm-table thead .fm-sticky-1, .fm-table thead .fm-sticky-2 { z-index: 4; background: #1e293b; }
.fm-class-group-row td {
    background: #1e293b; color: #fff; font-weight: 700; font-size: .75rem;
    padding: 6px 10px; position: sticky; left: 0;
}
.fm-cell { display: inline-block; min-width: 78px; text-align: center; border-radius: 6px; padding: 4px 8px; font-weight: 600; }
.fm-cell-date { display: block; font-weight: 400; font-size: .68rem; opacity: .75; }
.fm-paid    { background: #dcfce7; color: #166534; }
.fm-partial { background: #fef3c7; color: #92400e; }
.fm-unpaid  { background: #fee2e2; color: #991b1b; }
.fm-none    { background: #f1f5f9; color: #94a3b8; }
.fm-legend-swatch { display: inline-block; width: 14px; height: 14px; border-radius: 3px; margin-right: 5px; vertical-align: middle; }
</style>

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-table me-2" style="opacity:.8;"></i>Fee Matrix</h2>
            <p>Every student, every month, at a glance — paid, unpaid, overdue and admission charges in one grid</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="hero-stat">
                <div class="num">{{ $stats['total_students'] }}</div>
                <div class="lbl">Students</div>
            </div>
            <div class="hero-stat">
                <div class="num">{{ $stats['unpaid_admission'] }}</div>
                <div class="lbl">A/C Unpaid</div>
            </div>
            <a href="{{ route('fee-matrix.export', request()->query()) }}" class="btn-hero-ghost">
                <i class="fas fa-file-export"></i> Export CSV
            </a>
        </div>
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<div class="section-card card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('fee-matrix.index') }}">
            <div class="row g-2 align-items-end">

                <div class="col-md-2">
                    <label class="form-label small mb-1">Session</label>
                    <select name="session_id" class="form-select form-select-sm">
                        <option value="">All Sessions</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ $sessionId == $session->id ? 'selected' : '' }}>
                                {{ $session->session_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">Class</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="1" {{ (string)$statusFilter === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ (string)$statusFilter === '0' ? 'selected' : '' }}>Inactive</option>
                        <option value="all" {{ (string)$statusFilter === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">From Month</label>
                    <input type="month" name="from_month" class="form-control form-control-sm" value="{{ $fromMonth }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small mb-1">To Month</label>
                    <input type="month" name="to_month" class="form-control form-control-sm" value="{{ $toMonth }}">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-dark btn-sm w-100" style="border-radius:8px;">
                        <i class="fas fa-filter me-1"></i> Apply
                    </button>
                </div>

            </div>
        </form>

        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
            <small class="text-muted">
                Showing <strong>{{ \Carbon\Carbon::parse($fromMonth.'-01')->format('M Y') }}</strong>
                to <strong>{{ \Carbon\Carbon::parse($toMonth.'-01')->format('M Y') }}</strong>.
                Not seeing a voucher you expect? Widen the range:
            </small>
            @php
                $presets = [
                    'Last 6 months'  => [now()->subMonths(6)->format('Y-m'), now()->format('Y-m')],
                    'This year'      => [now()->startOfYear()->format('Y-m'), now()->endOfYear()->format('Y-m')],
                    'Last 12 months' => [now()->subMonths(12)->format('Y-m'), now()->addMonths(2)->format('Y-m')],
                ];
            @endphp
            @foreach($presets as $label => $range)
                <a href="{{ route('fee-matrix.index', array_merge(request()->except(['from_month','to_month']), ['from_month' => $range[0], 'to_month' => $range[1]])) }}"
                   class="btn btn-sm btn-outline-secondary" style="border-radius:20px; font-size:.75rem; padding:2px 12px;">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Legend ───────────────────────────────────────────────────────────── --}}
<div class="d-flex flex-wrap gap-3 align-items-center mb-3" style="font-size:.78rem; color:#475569;">
    <span><span class="fm-legend-swatch" style="background:#dcfce7;"></span> Fully Paid</span>
    <span><span class="fm-legend-swatch" style="background:#fef3c7;"></span> Partially Paid</span>
    <span><span class="fm-legend-swatch" style="background:#fee2e2;"></span> Unpaid (nothing paid yet)</span>
    <span><span class="fm-legend-swatch" style="background:#f1f5f9;"></span> No Voucher Generated Yet</span>
    <span><i class="fas fa-link" style="font-size:.7rem; opacity:.7;"></i> = one voucher covers multiple months — colour reflects the whole voucher's payment status</span>
</div>

{{-- ── Matrix ───────────────────────────────────────────────────────────── --}}
<div class="section-card card">
    <div class="fm-wrap">
        <table class="fm-table">
            <thead>
                <tr>
                    <th class="fm-sticky-1">Reg #</th>
                    <th class="fm-sticky-2">Student</th>
                    <th>Contact</th>
                    <th>Comments</th>
                    <th>Admission / Annual Fee</th>
                    @foreach($months as $month)
                        <th>{{ $month['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $palette = ['#eef2ff','#f8fafc','#fdf6e3','#fdf2f8','#ecfdf5','#fff7ed']; @endphp
                @forelse($groupedRows as $classId => $classRows)
                    @php $tint = $palette[$loop->index % count($palette)]; @endphp
                    <tr class="fm-class-group-row">
                        <td colspan="{{ 5 + count($months) }}">
                            {{ $classRows->first()['class']->class_name ?? 'Unassigned' }}
                            <span class="fw-normal opacity-75">({{ $classRows->count() }} students)</span>
                        </td>
                    </tr>

                    @foreach($classRows as $row)
                        <tr style="background:{{ $tint }};">
                            <td class="fm-sticky-1" style="background:{{ $tint }};">{{ $row['student']->admission_no }}</td>
                            <td class="fm-sticky-2" style="background:{{ $tint }};">
                                <a href="{{ route('students.show', $row['student']->id) }}" class="text-decoration-none fw-bold" style="color:#1e293b;">
                                    {{ $row['student']->student_name }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $row['student']->mobile_no ?: $row['student']->guardian_mobile ?: '—' }}</td>

                            <td style="max-width:180px; white-space:normal;">
                                <span style="font-size:.76rem; color:#475569;" title="{{ $row['comments'] }}">
                                    {{ $row['comments'] ? \Illuminate\Support\Str::limit($row['comments'], 40) : '—' }}
                                </span>
                                <a href="{{ route('enrollments.edit', $row['enrollment']->id) }}"
                                   class="ms-1" title="Edit comment" style="opacity:.5;">
                                    <i class="fas fa-pen" style="font-size:.65rem;"></i>
                                </a>
                            </td>

                            <td>
                                @if(!$row['admission'])
                                    <span class="fm-cell fm-none">N/A</span>
                                @else
                                    @php
                                        // A student can have more than one admission/annual
                                        // voucher — in that case voucher_id is null on
                                        // purpose (see FeeMatrixController), so link to the
                                        // student's profile instead of crashing on a
                                        // missing route parameter.
                                        $admHref = $row['admission']['voucher_id']
                                            ? route('fee-vouchers.print', $row['admission']['voucher_id'])
                                            : route('students.show', $row['student']->id);
                                        $admMulti = $row['admission']['voucher_count'] > 1;
                                    @endphp
                                    @if($row['admission']['state'] === 'paid')
                                        <a href="{{ $admHref }}" target="_blank" class="fm-cell fm-paid"
                                           style="text-decoration:none; display:inline-block;"
                                           title="{{ $admMulti ? $row['admission']['voucher_count'].' vouchers — view student profile' : '' }}">
                                            {{ number_format($row['admission']['paid_amount']) }}
                                            @if($admMulti)
                                                <span class="fm-cell-date">{{ $row['admission']['voucher_count'] }} vouchers</span>
                                            @elseif($row['admission']['paid_date'])
                                                <span class="fm-cell-date">{{ \Carbon\Carbon::parse($row['admission']['paid_date'])->format('d-M-y') }}</span>
                                            @endif
                                        </a>
                                    @elseif($row['admission']['state'] === 'partial')
                                        <a href="{{ $admHref }}" target="_blank" class="fm-cell fm-partial"
                                           style="text-decoration:none; display:inline-block;"
                                           title="{{ $admMulti ? $row['admission']['voucher_count'].' vouchers — view student profile' : '' }}">
                                            {{ number_format($row['admission']['paid_amount']) }} / {{ number_format($row['admission']['amount']) }}
                                            @if($admMulti)
                                                <span class="fm-cell-date">{{ $row['admission']['voucher_count'] }} vouchers</span>
                                            @elseif($row['admission']['paid_date'])
                                                <span class="fm-cell-date">{{ \Carbon\Carbon::parse($row['admission']['paid_date'])->format('d-M-y') }}</span>
                                            @endif
                                        </a>
                                    @else
                                        <a href="{{ $admHref }}" target="_blank" class="fm-cell fm-unpaid"
                                           style="text-decoration:none; display:inline-block;"
                                           title="{{ $admMulti ? $row['admission']['voucher_count'].' vouchers — view student profile' : '' }}">
                                            Unpaid
                                            <span class="fm-cell-date">Rs {{ number_format($row['admission']['amount']) }}{{ $admMulti ? ' ('.$row['admission']['voucher_count'].')' : '' }}</span>
                                        </a>
                                    @endif
                                @endif
                            </td>

                            @foreach($months as $month)
                                @php $cell = $row['cells'][$month['key']]; @endphp
                                <td>
                                    @if($cell['voucher_id'])
                                        <a href="{{ route('fee-vouchers.print', $cell['voucher_id']) }}"
                                           target="_blank"
                                           class="fm-cell fm-{{ $cell['state'] }}"
                                           style="text-decoration:none; display:inline-block; position:relative;"
                                           title="{{ $cell['voucher_no'] }}{{ $cell['multi_month'] ? ' — part of a multi-month voucher; colour reflects the whole voucher\'s payment status' : '' }} — click to view voucher">
                                            {{ $cell['label'] }}
                                            @if($cell['multi_month'])
                                                <i class="fas fa-link" style="font-size:.6rem; opacity:.7; margin-left:2px;"></i>
                                            @endif
                                            @if($cell['date'])
                                                <span class="fm-cell-date">{{ $cell['date'] }}</span>
                                            @endif
                                        </a>
                                    @else
                                        <span class="fm-cell fm-{{ $cell['state'] }}">
                                            {{ $cell['label'] }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ 5 + count($months) }}" class="text-center text-muted py-5">
                            No active students found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
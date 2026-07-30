@extends('layouts.dashboard')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════════════
     Self-contained CDN includes (layout has no Bootstrap / FA / scripts)
     ═══════════════════════════════════════════════════════════════════════ --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ── Screen styles ─────────────────────────────────────────────────────── */

.profile-hero {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-radius: 12px;
    color: #fff;
    padding: 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.profile-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
}
.profile-avatar {
    width: 110px; height: 110px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,.35);
    object-fit: cover;
    flex-shrink: 0;
}
.profile-avatar-initials {
    width: 110px; height: 110px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,.35);
    background: #0d6efd;
    display: flex; align-items: center; justify-content: center;
    font-size: 42px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.hero-badge {
    font-size: .75rem;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: .5px;
}

.section-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,.08);
    margin-bottom: 20px;
    overflow: hidden;
}
.section-card .card-header {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-card .card-header .section-icon {
    width: 28px; height: 28px;
    border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; color: #fff;
    flex-shrink: 0;
}
.section-card .card-header h6 {
    margin: 0; font-weight: 700; font-size: .9rem; color: #1e293b;
}

.info-table { margin: 0; }
.info-table tr { border-bottom: 1px solid #f1f5f9; }
.info-table tr:last-child { border-bottom: none; }
.info-table td { padding: 10px 20px; font-size: .875rem; vertical-align: middle; }
.info-table td.lbl {
    width: 38%;
    color: #64748b;
    font-weight: 600;
    white-space: nowrap;
}
.info-table td.val { color: #1e293b; font-weight: 500; }

.stat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.12);
    border-radius: 8px;
    padding: 8px 14px;
    font-size: .8rem;
    color: #fff;
}
.stat-pill strong { font-size: 1.1rem; }

/* ── Action buttons ────────────────────────────────────────────────────── */
.btn-action { border-radius: 8px; font-size: .85rem; font-weight: 600; padding: 8px 18px; }

/* ── Fee vouchers table ─────────────────────────────────────────────────── */
.fee-table thead th { background: #1e293b; color: #fff; font-size: .8rem; font-weight: 600; border: none; padding: 10px 14px; }
.fee-table tbody td { font-size: .85rem; padding: 9px 14px; vertical-align: middle; }
.fee-table tbody tr:hover { background: #f8fafc; }

/* ── Enrollment table ──────────────────────────────────────────────────── */
.enroll-table thead th { background: #0f172a; color: #fff; font-size: .8rem; font-weight: 600; border: none; padding: 10px 14px; }
.enroll-table tbody td { font-size: .85rem; padding: 9px 14px; vertical-align: middle; }

/* ── Photo upload quick-link ───────────────────────────────────────────── */
.photo-upload-hint {
    font-size: .75rem; color: rgba(255,255,255,.6);
    text-decoration: none; display: block; margin-top: 6px;
}
.photo-upload-hint:hover { color: #fff; }

/* ════════════════════════════════════════════════════════════════════════
   PDF PRINT STYLES
   ════════════════════════════════════════════════════════════════════════ */
@media print {
    body * { visibility: hidden !important; }
    #pdfTarget, #pdfTarget * { visibility: visible !important; }
    #pdfTarget { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }

    .pdf-header-bar {
        background: #1e293b !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .section-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    .info-table td { padding: 7px 16px !important; }

    a[href]:after { content: none !important; }
}

/* Start Fee Vouchers (and Receipts) on a fresh PDF page, keeping the
   personal/guardian/academic profile together on page 1. Kept outside
   @media print since html2pdf's "css" pagebreak mode reads this from the
   normally-rendered page, not a print-media context. It has no visible
   effect on normal screen scrolling. */
.pdf-page-break { page-break-before: always; break-before: page; }
</style>

{{-- ═══════════════════════════════════════════════════════════════════════
     PAGE HEADER (screen only)
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="d-flex justify-content-between align-items-center mb-3 no-print">

    <div>
        <h2 class="fw-bold mb-0" style="color:#1e293b; font-size:1.4rem;">
            <i class="fas fa-user-graduate me-2 text-primary"></i>Student Profile
        </h2>
        <small class="text-muted">Full profile &amp; academic record</small>
    </div>

    <div class="d-flex gap-2 flex-wrap">

        <button onclick="exportPDF()" class="btn btn-danger btn-action">
            <i class="fas fa-file-pdf me-1"></i> Export PDF
        </button>

        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning btn-action">
            <i class="fas fa-edit me-1"></i> Edit
        </a>

        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-action">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>

    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     PDF TARGET  — everything inside this div is exported
     ═══════════════════════════════════════════════════════════════════════ --}}
<div id="pdfTarget">

    {{-- ── PDF Header (only visible in PDF) ─────────────────────────────── --}}
    <div class="pdf-header-bar no-screen d-none d-print-flex"
         style="background:#1e293b; color:#fff; padding:16px 28px; border-radius:8px 8px 0 0;
                align-items:center; justify-content:space-between; margin-bottom:0;">
        <div>
            <div style="font-size:1.1rem; font-weight:700; letter-spacing:.5px;">PEACE ACADEMY</div>
            <div style="font-size:.75rem; opacity:.7;">Student Profile Report</div>
        </div>
        <div style="text-align:right; font-size:.75rem; opacity:.7;">
            Printed: {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

    {{-- ── Hero banner ──────────────────────────────────────────────────── --}}
    <div class="profile-hero">

        <div class="d-flex align-items-center gap-4 flex-wrap">

            {{-- Avatar --}}
            @if($student->photo_url)
                <img src="{{ $student->photo_url }}"
                     class="profile-avatar" alt="{{ $student->student_name }}"
                     onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('div'),{className:'profile-avatar-initials',textContent:'{{ strtoupper(substr($student->student_name, 0, 1)) }}'}));">
            @else
                <div class="profile-avatar-initials">
                    {{ strtoupper(substr($student->student_name, 0, 1)) }}
                </div>
            @endif

            {{-- Core info --}}
            <div class="flex-grow-1">

                <h3 class="fw-bold mb-1" style="font-size:1.5rem; letter-spacing:.3px;">
                    {{ $student->student_name }}
                </h3>

                <div class="mb-2 d-flex flex-wrap gap-2 align-items-center">

                    <span style="font-size:.85rem; opacity:.8;">
                        <i class="fas fa-id-badge me-1"></i>{{ $student->admission_no }}
                    </span>

                    @if($student->is_active)
                        <span class="hero-badge" style="background:#22c55e;">
                            <i class="fas fa-circle me-1" style="font-size:.55rem;"></i>Active
                        </span>
                    @else
                        <span class="hero-badge" style="background:#ef4444;">Inactive</span>
                    @endif

                    @if($student->gender)
                        <span class="hero-badge" style="background:rgba(255,255,255,.15);">
                            <i class="fas fa-{{ $student->gender === 'Female' ? 'venus' : 'mars' }} me-1"></i>
                            {{ $student->gender }}
                        </span>
                    @endif

                </div>

                {{-- Stat pills --}}
                <div class="d-flex flex-wrap gap-2">

                    @php $latestEnroll = $enrollments->first(); @endphp

                    @if($latestEnroll && $latestEnroll->class)
                        <span class="stat-pill">
                            <i class="fas fa-chalkboard-teacher" style="opacity:.7;"></i>
                            <strong>{{ $latestEnroll->class->class_name }}</strong>
                        </span>
                    @endif

                    @if($latestEnroll && $latestEnroll->session)
                        <span class="stat-pill">
                            <i class="fas fa-calendar-alt" style="opacity:.7;"></i>
                            <strong>{{ $latestEnroll->session->session_name }}</strong>
                        </span>
                    @endif

                    @if($student->admission_date)
                        <span class="stat-pill">
                            <i class="fas fa-door-open" style="opacity:.7;"></i>
                            Admitted <strong>{{ \Carbon\Carbon::parse($student->admission_date)->format('d M Y') }}</strong>
                        </span>
                    @endif

                </div>

            </div>

            {{-- Photo upload hint (screen only) --}}
            @unless($student->student_image)
                <div class="no-print text-end" style="min-width:100px;">
                    <a href="{{ route('students.edit', $student->id) }}" class="photo-upload-hint">
                        <i class="fas fa-camera me-1"></i>Add Photo
                    </a>
                </div>
            @endunless

        </div>

    </div>

    {{-- ── Two-column detail grid ──────────────────────────────────────── --}}
    <div class="row g-3">

        {{-- ────────── LEFT COLUMN ──────────────────────────────────────── --}}
        <div class="col-lg-6">

            {{-- Personal Information --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="section-icon" style="background:#3b82f6;">
                        <i class="fas fa-user"></i>
                    </span>
                    <h6>Personal Information</h6>
                </div>
                <div class="card-body p-0">
                    <table class="info-table table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="lbl">Student Name</td>
                                <td class="val">{{ $student->student_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Father Name</td>
                                <td class="val">{{ $student->father_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Mother Name</td>
                                <td class="val">{{ $student->mother_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Gender</td>
                                <td class="val">{{ $student->gender ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Date of Birth</td>
                                <td class="val">
                                    @if($student->date_of_birth)
                                        {{ \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Blood Group</td>
                                <td class="val">
                                    @if($student->blood_group)
                                        <span class="badge" style="background:#dc2626; font-size:.8rem;">
                                            {{ $student->blood_group }}
                                        </span>
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Religion</td>
                                <td class="val">{{ $student->religion ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">B-Form / CNIC</td>
                                <td class="val">
                                    <code style="font-size:.82rem; background:#f1f5f9; padding:2px 6px; border-radius:4px;">
                                        {{ $student->b_form_no ?? '—' }}
                                    </code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="section-icon" style="background:#10b981;">
                        <i class="fas fa-phone"></i>
                    </span>
                    <h6>Contact Information</h6>
                </div>
                <div class="card-body p-0">
                    <table class="info-table table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="lbl">Father Mobile</td>
                                <td class="val">
                                    @if($student->mobile_no)
                                        <i class="fas fa-mobile-alt me-1 text-muted" style="font-size:.75rem;"></i>
                                        {{ $student->mobile_no }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Mother Mobile</td>
                                <td class="val">
                                    @if($student->mother_mobile_no)
                                        <i class="fas fa-mobile-alt me-1 text-muted" style="font-size:.75rem;"></i>
                                        {{ $student->mother_mobile_no }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Father WhatsApp</td>
                                <td class="val">
                                    @if($student->whatsapp_no)
                                        <i class="fab fa-whatsapp me-1" style="color:#25d366; font-size:.85rem;"></i>
                                        {{ $student->whatsapp_no }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Mother WhatsApp</td>
                                <td class="val">
                                    @if($student->mother_whatsapp_no)
                                        <i class="fab fa-whatsapp me-1" style="color:#25d366; font-size:.85rem;"></i>
                                        {{ $student->mother_whatsapp_no }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Address</td>
                                <td class="val" style="white-space:pre-line;">{{ $student->address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Emergency Contact</td>
                                <td class="val">{{ $student->emergency_contact ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- ────────── RIGHT COLUMN ─────────────────────────────────────── --}}
        <div class="col-lg-6">

            {{-- Guardian / Parent --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="section-icon" style="background:#f59e0b;">
                        <i class="fas fa-users"></i>
                    </span>
                    <h6>Guardian / Parent Information</h6>
                </div>
                <div class="card-body p-0">
                    <table class="info-table table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="lbl">Guardian Name</td>
                                <td class="val">{{ $student->guardian_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Relation</td>
                                <td class="val">{{ $student->guardian_relation ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Guardian Mobile</td>
                                <td class="val">
                                    @if($student->guardian_mobile)
                                        <i class="fas fa-mobile-alt me-1 text-muted" style="font-size:.75rem;"></i>
                                        {{ $student->guardian_mobile }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Father Occupation</td>
                                <td class="val">{{ $student->father_occupation ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Academic Information --}}
            <div class="section-card card">
                <div class="card-header">
                    <span class="section-icon" style="background:#8b5cf6;">
                        <i class="fas fa-graduation-cap"></i>
                    </span>
                    <h6>Academic Information</h6>
                </div>
                <div class="card-body p-0">
                    <table class="info-table table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="lbl">Admission No</td>
                                <td class="val">
                                    <strong class="text-primary">{{ $student->admission_no }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Admission Date</td>
                                <td class="val">
                                    @if($student->admission_date)
                                        {{ \Carbon\Carbon::parse($student->admission_date)->format('d M Y') }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Current Class</td>
                                <td class="val">
                                    @if($latestEnroll && $latestEnroll->class)
                                        <span class="badge bg-success" style="font-size:.8rem;">
                                            {{ $latestEnroll->class->class_name }}
                                        </span>
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Session</td>
                                <td class="val">
                                    @if($latestEnroll && $latestEnroll->session)
                                        <span class="badge bg-info text-dark" style="font-size:.8rem;">
                                            {{ $latestEnroll->session->session_name }}
                                        </span>
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Roll No</td>
                                <td class="val">{{ $latestEnroll->roll_no ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Previous School</td>
                                <td class="val">{{ $student->previous_school ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Previous Class</td>
                                <td class="val">{{ $student->previous_class ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>{{-- /row --}}

    {{-- ── Enrollment History ──────────────────────────────────────────── --}}
    <div class="section-card card mt-1">
        <div class="card-header">
            <span class="section-icon" style="background:#0f172a;">
                <i class="fas fa-history"></i>
            </span>
            <h6>Enrollment History</h6>
            <span class="ms-auto badge bg-secondary" style="font-size:.72rem;">
                {{ $enrollments->count() }} record(s)
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table enroll-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Session</th>
                            <th>Class</th>
                            <th>Roll No</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrollments as $i => $enrollment)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>{{ $enrollment->session->session_name ?? '—' }}</td>
                                <td>{{ $enrollment->class->class_name ?? '—' }}</td>
                                <td>{{ $enrollment->roll_no ?? '—' }}</td>
                                <td>
                                    @if($enrollment->enrollment_date)
                                        {{ \Carbon\Carbon::parse($enrollment->enrollment_date)->format('d M Y') }}
                                    @else —
                                    @endif
                                </td>
                                <td>
                                    @if($enrollment->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($enrollment->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox me-1"></i> No enrollment records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Siblings ─────────────────────────────────────────────────────── --}}
    @if($student->family_code)
    <div class="section-card card mt-3">
        <div class="card-header">
            <span class="section-icon" style="background:#ec4899;">
                <i class="fas fa-people-roof"></i>
            </span>
            <h6>Siblings</h6>
            <span class="ms-auto badge bg-secondary" style="font-size:.72rem;">
                Family Code: {{ $student->family_code }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:.85rem;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end no-print">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siblings as $sibling)
                            @php $sibEnroll = $sibling->enrollments->last(); @endphp
                            <tr>
                                <td>{{ $sibling->student_name }}</td>
                                <td>{{ $sibling->admission_no }}</td>
                                <td>{{ $sibEnroll && $sibEnroll->class ? $sibEnroll->class->class_name : '—' }}</td>
                                <td class="text-end {{ $sibling->sibling_outstanding > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    Rs {{ number_format($sibling->sibling_outstanding) }}
                                </td>
                                <td class="text-end no-print">
                                    <a href="{{ route('students.show', $sibling->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No other siblings linked yet.
                                </td>
                            </tr>
                        @endforelse
                        <tr style="background:#f8fafc;">
                            <td colspan="3" class="fw-bold text-end">Family Total Outstanding</td>
                            <td class="text-end fw-bold {{ $siblingsOutstanding > 0 ? 'text-danger' : 'text-success' }}">
                                Rs {{ number_format($siblingsOutstanding) }}
                            </td>
                            <td class="no-print"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Fee Vouchers ────────────────────────────────────────────────── --}}
    <div class="section-card card mt-3 pdf-page-break">
        <div class="card-header">
            <span class="section-icon" style="background:#dc2626;">
                <i class="fas fa-receipt"></i>
            </span>
            <h6>Fee Vouchers</h6>
            <span class="ms-auto badge bg-secondary" style="font-size:.72rem;">
                {{ $vouchers->count() }} voucher(s)
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table fee-table mb-0">
                    <thead>
                        <tr>
                            <th>Voucher No</th>
                            <th>Period</th>
                            <th>Total</th>
                            <th>Discount</th>
                            <th>Payable</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                            <tr>
                                <td>
                                    <a href="{{ route('fee-vouchers.print', $voucher->id) }}"
                                       target="_blank"
                                       class="fw-bold text-primary text-decoration-none"
                                       style="font-size:.82rem;"
                                       title="Open printable voucher">
                                        {{ $voucher->voucher_no ?? '—' }}
                                        <i class="fas fa-external-link-alt ms-1" style="font-size:.65rem; opacity:.6;"></i>
                                    </a>
                                </td>
                                <td class="text-muted" style="white-space:nowrap;">
                                    @if($voucher->period_from)
                                        {{ \Carbon\Carbon::parse($voucher->period_from)->format('M Y') }}
                                        @if($voucher->period_to && $voucher->period_to !== $voucher->period_from)
                                            – {{ \Carbon\Carbon::parse($voucher->period_to)->format('M Y') }}
                                        @endif
                                    @else —
                                    @endif
                                </td>
                                <td>Rs {{ number_format($voucher->total_amount ?? 0) }}</td>
                                <td class="text-success">
                                    @if($voucher->discount > 0)
                                        – Rs {{ number_format($voucher->discount) }}
                                    @else —
                                    @endif
                                </td>
                                <td class="fw-bold">Rs {{ number_format($voucher->payable_amount ?? 0) }}</td>
                                <td class="text-success">Rs {{ number_format($voucher->paid_amount ?? 0) }}</td>
                                <td class="{{ ($voucher->balance_amount ?? 0) > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    Rs {{ number_format($voucher->balance_amount ?? 0) }}
                                </td>
                                <td style="white-space:nowrap;" class="text-muted">
                                    @if($voucher->due_date)
                                        {{ \Carbon\Carbon::parse($voucher->due_date)->format('d M Y') }}
                                    @else —
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $st = strtolower($voucher->status ?? '');
                                        $badge = match($st) {
                                            'paid'    => 'bg-success',
                                            'partial' => 'bg-warning text-dark',
                                            'unpaid'  => 'bg-danger',
                                            default   => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}" style="font-size:.75rem;">
                                        {{ ucfirst($voucher->status ?? 'Unknown') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox me-1"></i> No fee vouchers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($vouchers->count() > 0)
                        @php
                            $totPayable = $vouchers->sum('payable_amount');
                            $totPaid    = $vouchers->sum('paid_amount');
                            $totBal     = $vouchers->sum('balance_amount');
                        @endphp
                        <tfoot>
                            <tr style="background:#f8fafc; font-weight:700;">
                                <td colspan="4" class="text-end pe-3" style="font-size:.82rem; color:#64748b;">
                                    TOTALS
                                </td>
                                <td class="fw-bold">Rs {{ number_format($totPayable) }}</td>
                                <td class="text-success fw-bold">Rs {{ number_format($totPaid) }}</td>
                                <td class="{{ $totBal > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                    Rs {{ number_format($totBal) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif

                </table>
            </div>
        </div>
    </div>

    {{-- ── Receipts ─────────────────────────────────────────────────────── --}}
    <div class="section-card card mt-3">
        <div class="card-header">
            <span class="section-icon" style="background:#0891b2;">
                <i class="fas fa-file-invoice-dollar"></i>
            </span>
            <h6>Payment Receipts</h6>
            <span class="ms-auto badge bg-secondary" style="font-size:.72rem;">
                {{ $receipts->count() }} receipt(s)
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table fee-table mb-0">
                    <thead>
                        <tr>
                            <th>Receipt No</th>
                            <th>Voucher No</th>
                            <th>Payment Date</th>
                            <th>Amount Paid</th>
                            <th>Method</th>
                            <th>Reference No</th>
                            <th>Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td>
                                    <a href="{{ route('fee-payments.edit', $receipt->id) }}"
                                       class="fw-bold text-primary text-decoration-none"
                                       style="font-size:.82rem;"
                                       title="Open receipt">
                                        {{ $receipt->receipt_no ?? '#'.$receipt->id }}
                                        <i class="fas fa-external-link-alt ms-1" style="font-size:.65rem; opacity:.6;"></i>
                                    </a>
                                </td>
                                <td class="text-muted" style="font-size:.82rem;">
                                    {{ $receipt->voucher->voucher_no ?? '—' }}
                                </td>
                                <td class="text-muted" style="white-space:nowrap;">
                                    @if($receipt->payment_date)
                                        {{ \Carbon\Carbon::parse($receipt->payment_date)->format('d M Y') }}
                                    @else —
                                    @endif
                                </td>
                                <td class="fw-bold text-success">Rs {{ number_format($receipt->amount_paid ?? 0) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark" style="font-size:.75rem;">
                                        {{ $receipt->payment_method ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:.82rem;">{{ $receipt->reference_no ?? '—' }}</td>
                                <td class="text-muted" style="font-size:.82rem;">{{ $receipt->received_by ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox me-1"></i> No payment receipts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($receipts->count() > 0)
                        <tfoot>
                            <tr style="background:#f8fafc; font-weight:700;">
                                <td colspan="3" class="text-end pe-3" style="font-size:.82rem; color:#64748b;">
                                    TOTAL RECEIVED
                                </td>
                                <td class="fw-bold text-success">Rs {{ number_format($receipts->sum('amount_paid')) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- PDF footer --}}
    <div class="d-none d-print-block mt-4 pt-3"
         style="border-top:2px solid #e2e8f0; font-size:.72rem; color:#94a3b8; text-align:center;">
        Peace Academy ERP &nbsp;|&nbsp; Student Profile &nbsp;|&nbsp;
        {{ $student->student_name }} ({{ $student->admission_no }}) &nbsp;|&nbsp;
        Generated {{ now()->format('d M Y') }}
    </div>

</div>{{-- /#pdfTarget --}}

{{-- ═══════════════════════════════════════════════════════════════════════
     Scripts — Bootstrap + html2pdf
     ═══════════════════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
function exportPDF() {
    const element = document.getElementById('pdfTarget');

    // Temporarily show PDF-only elements before capture
    document.querySelectorAll('.d-print-flex, .d-print-block').forEach(el => {
        el.style.display = el.classList.contains('d-print-flex') ? 'flex' : 'block';
    });
    document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');

    const options = {
        margin:       [10, 10, 10, 10],
        filename:     'Profile-{{ $student->student_name }}-{{ $student->admission_no }}.pdf',
        image:        { type: 'jpeg', quality: 0.97 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
    };

    html2pdf().set(options).from(element).save().then(() => {
        // Restore visibility after export
        document.querySelectorAll('.d-print-flex, .d-print-block').forEach(el => {
            el.style.display = '';
        });
        document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
    });
}
</script>

@endsection
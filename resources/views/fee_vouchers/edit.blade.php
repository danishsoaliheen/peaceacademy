@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

    .voucher-container{
        background:var(--bg-surface, #fff);
        padding:20px;
        border-radius:8px;
        box-shadow:var(--card-shadow, 0 0 10px rgba(0,0,0,0.08));
    }

    table{
        width:100%;
        border-collapse:collapse;
        margin-bottom:20px;
    }

    table th,
    table td{
        border:1px solid var(--border-color, #ddd);
        padding:10px;
    }

    table th{
        background:var(--bg-body, #f0f0f0);
        color:var(--text-secondary, #475569);
    }

    input,
    select,
    textarea{
        width:100%;
        padding:8px;
        border:1px solid var(--border-color, #ccc);
        border-radius:4px;
        background:var(--input-bg, #fff);
        color:var(--text-primary, #1e293b);
    }

    input:focus, select:focus {
        outline: none;
        border-color: var(--accent-primary, #0d6efd);
        box-shadow: 0 0 0 3px rgba(45,108,181,.15);
    }

    input[readonly],
    input:disabled,
    select:disabled{
        background:var(--bg-body, #f1f5f9);
        color:var(--text-secondary, #64748b);
        cursor:not-allowed;
    }

    .btn{
        padding:10px 15px;
        border:none;
        border-radius:4px;
        cursor:pointer;
        text-decoration:none;
    }

    .btn-primary{
        background:var(--accent-primary, #0d6efd);
        color:white;
    }

    .btn-primary:disabled{
        background:#93c5fd;
        cursor:not-allowed;
    }

    .btn-danger{
        background:#dc2626;
        color:white;
    }

    .btn-danger:disabled{
        background:#fca5a5;
        cursor:not-allowed;
    }

    .row-no{
        text-align:center;
        font-weight:bold;
    }

    .lock-banner{
        background:rgba(183,134,47,.08);
        border:1px solid rgba(183,134,47,.35);
        color:var(--accent-gold, #92400e);
        padding:12px 16px;
        border-radius:6px;
        font-size:.85rem;
        margin-bottom:18px;
        display:flex;
        align-items:flex-start;
        gap:10px;
    }

    .lock-banner i{
        margin-top:2px;
        font-size:1rem;
    }

    .lock-banner a{
        color:var(--accent-gold, #92400e);
        font-weight:700;
        text-decoration:underline;
    }

    .status-chip{
        display:inline-block;
        font-size:.7rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.05em;
        padding:3px 10px;
        border-radius:20px;
        margin-left:10px;
        vertical-align:middle;
    }

    .chip-partial{ background:#fef9c3; color:#92400e; }
    .chip-unpaid{ background:#fee2e2; color:#dc2626; }

</style>

<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2>
                <i class="fas fa-file-pen me-2" style="opacity:.8;"></i>Edit Fee Voucher
                @php $statusLower = strtolower($voucher->status); @endphp
                <span class="status-chip chip-{{ $statusLower }}">{{ ucfirst($voucher->status) }}</span>
            </h2>
            <p>Voucher {{ $voucher->voucher_no }}</p>
        </div>
        <a href="{{ route('fee-vouchers.index') }}" class="btn-hero-ghost">
            <i class="fas fa-arrow-left"></i> Back to Vouchers
        </a>
    </div>
</div>

<div class="section-card card">
    <div class="card-body">

        {{-- ════════════════════════════════════════════════
             LOCK NOTICE — shown when payment(s) already exist
        ════════════════════════════════════════════════ --}}
        @if($hasPayments)
        <div class="lock-banner">
            <i class="fas fa-lock"></i>
            <div>
                <strong>Payment already recorded against this voucher.</strong>
                Fee items, student and totals are locked to keep paid/balance amounts accurate.
                You can still update the <strong>period, due date, and notes</strong> below.
                <br>
                If the <strong>amount received or payment method was entered wrong</strong>, edit it below
                — the voucher balance will recalculate automatically.

                @if($voucher->payments->count() > 0)
                <div style="margin-top:10px;background:var(--bg-surface,#fff);border:1px solid rgba(183,134,47,.35);border-radius:6px;padding:8px 12px">
                    @foreach($voucher->payments->sortByDesc('payment_date') as $pmt)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(183,134,47,.2)' : '' }}">
                            <span style="font-size:.8rem;color:var(--accent-gold,#78350f)">
                                <strong>{{ $pmt->receipt_no }}</strong>
                                — Rs. {{ number_format($pmt->amount_paid, 0) }}
                                on {{ \Carbon\Carbon::parse($pmt->payment_date)->format('d-M-Y') }}
                                ({{ $pmt->payment_method }})
                            </span>
                            <a href="{{ route('fee-payments.edit', $pmt->id) }}" style="font-size:.75rem;font-weight:700;color:var(--accent-primary,#1d4ed8);text-decoration:underline">
                                Edit
                            </a>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle me-1"></i>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST"
              action="{{ route('fee-vouchers.update', $voucher->id) }}">

            @csrf
            @method('PUT')

            <table>

                <tr>

                    <td width="20%">Class</td>

                    <td>

                        <select id="class_id" disabled>

                            <option value="">Select Class</option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}">

                                    {{ $class->class_name }}

                                </option>

                            @endforeach

                        </select>

                    </td>

                </tr>

                <tr>

                    <td>Student</td>

                    <td>

                        <select name="student_id"
                                id="student_id"
                                {{ $hasPayments ? 'disabled' : 'required' }}>

                            <option value="">Select Student</option>

                            @foreach($students as $student)

                                @php

                                    $latestEnrollment =
                                        $student->enrollments->last();

                                    $classId =
                                        $latestEnrollment->class_id ?? '';

                                    $className =
                                        $latestEnrollment->class->class_name ?? '';

                                @endphp

                                <option value="{{ $student->id }}"
                                        data-class="{{ $classId }}"
                                        data-class-name="{{ $className }}"
                                        {{ $voucher->student_id == $student->id ? 'selected' : '' }}>

                                    {{ $student->student_name }}
                                    -
                                    {{ $className }}
                                    -
                                    {{ $student->admission_no }}

                                </option>

                            @endforeach

                        </select>

                        {{-- Keep student_id submitting even when the select is disabled --}}
                        @if($hasPayments)
                            <input type="hidden" name="student_id" value="{{ $voucher->student_id }}">
                        @endif

                    </td>

                </tr>

                <tr>

                    <td>Selected Student Class</td>

                    <td>

                        <input type="text"
                               id="student_class"
                               readonly>

                    </td>

                </tr>

                @php
                    $periodFromFilled = (bool) $voucher->period_from;
                    $periodToFilled   = (bool) $voucher->period_to;
                    $periodFromValue  = $periodFromFilled
                        ? $voucher->period_from->format('Y-m-d')
                        : ($voucher->due_date ? $voucher->due_date->copy()->startOfMonth()->format('Y-m-d') : '');
                    $periodToValue    = $periodToFilled
                        ? $voucher->period_to->format('Y-m-d')
                        : ($voucher->due_date ? $voucher->due_date->copy()->endOfMonth()->format('Y-m-d') : '');
                @endphp

                <tr>

                    <td>Period From</td>

                    <td>

                        <input type="date"
                               name="period_from"
                               value="{{ $periodFromValue }}">

                        @unless($periodFromFilled)
                            <small style="color:#b45309;">Not recorded originally — defaulted from due date, please confirm.</small>
                        @endunless

                    </td>

                </tr>

                <tr>

                    <td>Period To</td>

                    <td>

                        <input type="date"
                               name="period_to"
                               value="{{ $periodToValue }}">

                        @unless($periodToFilled)
                            <small style="color:#b45309;">Not recorded originally — defaulted from due date, please confirm.</small>
                        @endunless

                    </td>

                </tr>

                <tr>

                    <td>Due Date</td>

                    <td>

                        <input type="date"
                               name="due_date"
                               value="{{ $voucher->due_date ? $voucher->due_date->format('Y-m-d') : now()->addDays(7)->format('Y-m-d') }}">

                    </td>

                </tr>

            </table>

            <h3>
                Fee Items
                @if($hasPayments)
                    <small style="font-size:.7rem;color:#92400e;font-weight:600">(locked — payment already recorded)</small>
                @endif
            </h3>

            <table id="feeTable">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Fee Type</th>
                        <th>Description</th>
                        <th>Month</th>
                        <th>Months</th>
                        <th>Amount</th>
                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($voucher->items as $index => $item)

                    <tr>

                        <td class="row-no">
                            {{ $index + 1 }}
                        </td>

                        <td>

                            <select name="fee_type_id[]"
                                    class="fee_type"
                                    {{ $hasPayments ? 'disabled' : '' }}>

                                <option value="">
                                    Select Fee Type
                                </option>

                                @foreach($feeTypes as $fee)

                                    <option value="{{ $fee->id }}"
                                            data-amount="{{ $fee->default_amount }}"
                                            {{ $item->fee_type_id == $fee->id ? 'selected' : '' }}>

                                        {{ $fee->name }}

                                    </option>

                                @endforeach

                            </select>

                            @if($hasPayments)
                                <input type="hidden" name="fee_type_id[]" value="{{ $item->fee_type_id }}">
                            @endif

                        </td>

                        <td>

                            <input type="text"
                                   name="description[]"
                                   value="{{ $item->description }}"
                                   {{ $hasPayments ? 'readonly' : '' }}>

                        </td>

                        <td>

                            <input type="date"
                                   name="month[]"
                                   value="{{ $item->month }}"
                                   {{ $hasPayments ? 'readonly' : '' }}>

                        </td>

                        <td>

                            <input type="number"
                                   name="months_count[]"
                                   class="months_count"
                                   value="{{ $item->months_count }}"
                                   {{ $hasPayments ? 'readonly' : '' }}>

                        </td>

                        <td>

                            <input type="number"
                                   name="amount[]"
                                   class="amount"
                                   value="{{ $item->amount }}"
                                   {{ $hasPayments ? 'readonly' : '' }}>

                        </td>

                        <td>

                            <button type="button"
                                    class="btn btn-danger"
                                    onclick="removeRow(this)"
                                    {{ $hasPayments ? 'disabled' : '' }}>

                                X

                            </button>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

            <button type="button"
                    class="btn btn-primary"
                    onclick="addRow()"
                    {{ $hasPayments ? 'disabled' : '' }}>

                Add Row

            </button>

            <br><br>

            <table>

                <tr>

                    <td width="20%">Total Amount</td>

                    <td>

                        <input type="number"
                               name="total_amount"
                               id="total_amount"
                               value="{{ $voucher->total_amount }}"
                               readonly>

                    </td>

                </tr>

                <tr>

                    <td>Discount</td>

                    <td>

                        <input type="number"
                               name="discount"
                               id="discount"
                               value="{{ $voucher->discount }}"
                               {{ $hasPayments ? 'readonly' : '' }}>

                    </td>

                </tr>

                <tr>

                    <td>Payable Amount</td>

                    <td>

                        <input type="number"
                               name="payable_amount"
                               id="payable_amount"
                               value="{{ $voucher->payable_amount }}"
                               readonly>

                    </td>

                </tr>

                <tr>

                    <td>Amount in Words</td>

                    <td>

                        <input type="text"
                               name="amount_in_words"
                               id="amount_words"
                               value="{{ $voucher->amount_in_words }}"
                               readonly>

                    </td>

                </tr>

                @if($hasPayments)
                <tr>

                    <td>Amount Received</td>

                    <td>

                        <input type="text"
                               value="Rs. {{ number_format($voucher->paid_amount, 0) }}"
                               readonly
                               style="font-weight:700;color:#15803d">

                    </td>

                </tr>

                <tr>

                    <td>Balance Due</td>

                    <td>

                        <input type="text"
                               value="Rs. {{ number_format($voucher->balance_amount, 0) }}"
                               readonly
                               style="font-weight:700;color:#dc2626">

                    </td>

                </tr>
                @endif

                <tr>

                    <td>Notes</td>

                    <td>

                        <textarea name="notes"
                                  rows="3">{{ $voucher->notes }}</textarea>

                    </td>

                </tr>

            </table>

            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Update Voucher
                </button>

                <a href="{{ route('fee-vouchers.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">
                    Cancel
                </a>
            </div>

        </form>

    </div>
</div>

<script>

const hasPayments = {{ $hasPayments ? 'true' : 'false' }};

function updateRowNumbers()
{
    document.querySelectorAll('#feeTable tbody tr')
        .forEach(function(row, index){

        row.querySelector('.row-no').innerText =
            index + 1;
    });
}

/* ═══════════════════════════════════════════════════
 * Class Fee Structure map — class_id → fee_type_id → unit_rate
 * ═══════════════════════════════════════════════════ */
const classFeeMap = {!! json_encode($classFeeMap) !!};

function getUnitRate(feeTypeId) {
    const sel = document.getElementById('student_id');
    const classId = sel ? (sel.options[sel.selectedIndex]?.dataset.class || '') : '';
    if (!classId || !feeTypeId) return null;
    return (classFeeMap[classId] && classFeeMap[classId][feeTypeId] !== undefined)
        ? parseFloat(classFeeMap[classId][feeTypeId])
        : null;
}

function calculateTotals()
{
    // Totals stay locked once payment exists — controller ignores these fields anyway,
    // but keep the display accurate instead of re-summing disabled/readonly inputs.
    if (hasPayments) {
        return;
    }

    let total = 0;

    document.querySelectorAll('.amount')
        .forEach(function(input){

        total += parseFloat(input.value) || 0;
    });

    document.getElementById('total_amount').value =
        total;

    let discount =
        parseFloat(document.getElementById('discount').value) || 0;

    let payable = total - discount;

    if(payable < 0){
        payable = 0;
    }

    document.getElementById('payable_amount').value =
        payable;
}

document.addEventListener('input', function(e){

    if(
        e.target.classList.contains('amount')
        ||
        e.target.id === 'discount'
    ){
        // If manually editing amount, clear stored unit rate so qty won't override it
        if (e.target.classList.contains('amount')) {
            const row = e.target.closest('tr');
            if (row) row.dataset.unitRate = '';
        }
        calculateTotals();
    }
});

function removeRow(button)
{
    if (hasPayments) return;

    let tbody =
        document.querySelector('#feeTable tbody');

    if(tbody.rows.length > 1){

        button.closest('tr').remove();

        updateRowNumbers();

        calculateTotals();
    }
}

function addRow()
{
    if (hasPayments) return;

    const tbody = document.querySelector('#feeTable tbody');

    // Clone the fee-type <select>'s option list from the first existing row
    // so new rows always match the server-rendered fee types — no JS duplication to drift out of sync.
    const templateSelect = document.querySelector('#feeTable tbody select.fee_type');
    const optionsHtml     = templateSelect ? templateSelect.innerHTML : '<option value="">Select Fee Type</option>';

    const rowCount = tbody.rows.length + 1;

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="row-no">${rowCount}</td>
        <td>
            <select name="fee_type_id[]" class="fee_type">
                ${optionsHtml}
            </select>
        </td>
        <td>
            <input type="text" name="description[]" value="">
        </td>
        <td>
            <input type="date" name="month[]" value="">
        </td>
        <td>
            <input type="number" name="months_count[]" class="months_count" value="1">
        </td>
        <td>
            <input type="number" name="amount[]" class="amount" value="0">
        </td>
        <td>
            <button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button>
        </td>
    `;

    // Reset the cloned select to "no selection" — cloning options carries over the
    // first row's `selected` option otherwise.
    tr.querySelector('select.fee_type').value = '';

    tbody.appendChild(tr);

    updateRowNumbers();
    calculateTotals();
}

// Fee type change → look up class-specific rate × qty, fill amount
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('fee_type') && !hasPayments) {
        const row       = e.target.closest('tr');
        const feeTypeId = e.target.value;
        const qtyInput  = row.querySelector('.months_count');
        const amtInput  = row.querySelector('.amount');

        const classRate   = getUnitRate(feeTypeId);
        const defaultRate = parseFloat(e.target.options[e.target.selectedIndex]?.dataset.amount) || 0;
        const unitRate    = (classRate !== null) ? classRate : defaultRate;

        row.dataset.unitRate = unitRate;

        const qty = parseFloat(qtyInput?.value) || 1;
        if (unitRate > 0) amtInput.value = unitRate * qty;
        calculateTotals();
    }
});

// Qty change → recompute amount = unitRate × qty
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('months_count') && !hasPayments) {
        const row      = e.target.closest('tr');
        const unitRate = parseFloat(row.dataset.unitRate) || 0;
        const qty      = parseFloat(e.target.value) || 1;
        if (unitRate > 0) {
            row.querySelector('.amount').value = unitRate * qty;
        }
        calculateTotals();
    }
});

const studentSelectEl = document.getElementById('student_id');

if (studentSelectEl) {
    studentSelectEl.addEventListener('change', function(){

        let selected =
            this.options[this.selectedIndex];

        document.getElementById('student_class').value =
            selected.dataset.className || '';
    });
}

window.onload = function(){

    let studentSelect =
        document.getElementById('student_id');

    if (studentSelect) {
        let selected =
            studentSelect.options[studentSelect.selectedIndex];

        document.getElementById('student_class').value =
            selected.dataset.className || '';
    }

    calculateTotals();

    updateRowNumbers();
};

</script>

@endsection
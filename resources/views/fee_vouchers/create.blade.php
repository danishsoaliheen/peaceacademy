@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

    * { box-sizing: border-box; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table th, table td { border: 1px solid var(--border-color, #dcdcdc); padding: 10px; vertical-align: middle; }
    table th { background: var(--bg-body, #f8fafc); text-align: left; font-size: 13px; color: var(--text-secondary, #475569); }

    input, select, textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid var(--border-color, #ccc);
        border-radius: 4px;
        font-size: 13px;
        background: var(--input-bg, #fff);
        color: var(--text-primary, #1e293b);
    }

    input:focus, select:focus {
        outline: none;
        border-color: var(--accent-primary, #0d6efd);
        box-shadow: 0 0 0 3px rgba(45,108,181,.15);
    }

    textarea { resize: vertical; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; }
    .btn-primary { background: var(--accent-primary, #0d6efd); color: white; }
    .btn-primary:hover { background: var(--accent-primary-hover, #0b5ed7); }
    .btn-danger { background: #dc2626; color: white; }
    .btn:hover { opacity: 0.9; }
    .row-no { text-align: center; font-weight: bold; }

    /* ── Previous Balance Panel ──────────────────────── */
    #prev-balance-panel {
        display: none;
        background: rgba(183,134,47,.08);
        border: 1px solid rgba(183,134,47,.35);
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }

    #prev-balance-panel.has-balance {
        display: block;
    }

    #prev-balance-panel .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    #prev-balance-panel .panel-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--accent-gold, #c2410c);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #prev-balance-panel .total-amount {
        font-size: 22px;
        font-weight: 800;
        color: var(--accent-gold, #c2410c);
    }

    .prev-breakdown table {
        margin-bottom: 0;
        font-size: 12px;
    }

    .prev-breakdown thead tr { background: rgba(183,134,47,.12); }
    .prev-breakdown th { font-size: 11px; color: var(--accent-gold, #9a3412); }
    .prev-breakdown td { background: var(--bg-surface, #fff); }
    .prev-breakdown input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; }

    .prev-balance-loader {
        display: none;
        color: var(--text-secondary, #94a3b8);
        font-size: 13px;
        padding: 8px 0;
    }

    /* ── Totals Row ──────────────────────────────────── */
    #total-row { background: var(--bg-body, #f8fafc); }
    #total-row td { font-weight: 700; }
    #grand-total-display { font-size: 20px; font-weight: 800; color: var(--accent-primary, #0d6efd); }
    #prev-bal-row { display: none; background: rgba(183,134,47,.08); }

</style>

<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-receipt me-2" style="opacity:.8;"></i>New Fee Voucher</h2>
            <p>Create a fee voucher and optionally roll in any previous outstanding balance</p>
        </div>
        <a href="{{ route('fee-vouchers.index') }}" class="btn-hero-ghost">
            <i class="fas fa-arrow-left"></i> Back to Vouchers
        </a>
    </div>
</div>

<div class="section-card card">
<div class="card-body">

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

    <form method="POST" action="{{ route('fee-vouchers.store') }}" id="voucherForm">

        @csrf

        {{-- ── Student Selection ── --}}
        <table>

            <tr>
                <td width="18%"><strong>Class</strong></td>
                <td>
                    <select id="class_id" class="form-control">
                        <option value="">— Select Class —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>

            <tr>
                <td><strong>Student</strong></td>
                <td>
                    <select name="student_id" id="student_id" required>
                        <option value="">— Select Student —</option>
                        @foreach($students as $student)
                            @php
                                $latestEnrollment = $student->enrollments->last();
                                $classId   = $latestEnrollment->class_id ?? '';
                                $className = $latestEnrollment->class->class_name ?? '';
                            @endphp
                            <option value="{{ $student->id }}"
                                    data-class="{{ $classId }}"
                                    data-class-name="{{ $className }}"
                                    {{ (old('student_id', $preselectedStudentId) == $student->id) ? 'selected' : '' }}>
                                {{ $student->student_name }} — {{ $className }} — {{ $student->admission_no }}
                            </option>
                        @endforeach
                    </select>
                </td>
            </tr>

            <tr>
                <td>Class (auto)</td>
                <td>
                    <input type="text" id="student_class" readonly
                           style="background:#f8fafc; color:#64748b;">
                </td>
            </tr>

            <tr>
                <td>Voucher Type</td>
                <td>
                    <select name="voucher_type">
                        <option value="monthly">Monthly Fee</option>
                        <option value="admission">Admission Fee</option>
                        <option value="manual">Manual</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Period From</td>
                <td><input type="date" name="period_from" required value="{{ old('period_from', date('Y-m-01')) }}"></td>
            </tr>

            <tr>
                <td>Period To</td>
                <td><input type="date" name="period_to" required value="{{ old('period_to', date('Y-m-t')) }}"></td>
            </tr>

            <tr>
                <td>Due Date</td>
                <td><input type="date" name="due_date" required value="{{ old('due_date', date('Y-m-t')) }}"></td>
            </tr>

        </table>

        {{-- ── Previous Balance Loader Indicator ── --}}
        <div class="prev-balance-loader" id="prev-balance-loader">
            ⏳ Checking previous balance…
        </div>

        {{-- ── Previous Balance Panel (shown only when student has any open voucher) ── --}}
        <div id="prev-balance-panel">

            <div class="panel-header">
                <div class="panel-title">
                    ⚠️ Previous Outstanding Vouchers
                </div>
                <div class="total-amount" id="prev-bal-amount-display">Rs. 0</div>
            </div>

            <div class="prev-breakdown" id="prev-breakdown-table"></div>

            <div style="margin-top:12px; font-size:12px; color:#9a3412; background:white;
                        border:1px solid #fed7aa; border-radius:6px; padding:10px 14px;">
                Tick any voucher(s) above to roll their balance into this new voucher as a line item.
                Every voucher you tick will be marked <strong>Carried Forward (C.F)</strong> and its
                balance zeroed once this voucher is saved — so nothing gets counted twice. Leave a
                voucher unticked to keep it open and separate. The amount is always recalculated from
                the server at save time, not just the figure shown here.
            </div>

        </div>

        {{-- ── Fee Line Items ── --}}
        <h3 style="margin: 8px 0 12px; font-size:15px; color:#1e293b;">Fee Items</h3>

        <table id="feeTable">

            <thead>
                <tr>
                    <th class="row-no" width="40">#</th>
                    <th>Fee Type</th>
                    <th width="220">Description</th>
                    <th width="130">Month</th>
                    <th width="70">Qty</th>
                    <th width="130">Amount (Rs.)</th>
                    <th width="60">Remove</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td class="row-no">1</td>
                    <td>
                        <select name="fee_type_id[]" class="fee_type" required>
                            <option value="">— Fee Type —</option>
                            @foreach($feeTypes as $ft)
                                <option value="{{ $ft->id }}"
                                        data-amount="{{ $ft->default_amount ?? '' }}">
                                    {{ $ft->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="description[]" class="description"></td>
                    <td><input type="month" name="month[]" class="month" value="{{ date('Y-m') }}"></td>
                    <td><input type="number" name="months_count[]" class="months_count" value="1" min="1"></td>
                    <td><input type="number" name="amount[]" class="amount" placeholder="0" min="0" step="1"></td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-danger" onclick="removeRow(this)">✕</button>
                    </td>
                </tr>
            </tbody>

        </table>

        <button type="button" class="btn btn-primary" onclick="addRow()" style="margin-bottom:20px;">
            + Add Row
        </button>

        {{-- ── Totals ── --}}
        <table style="width:350px; margin-left:auto;">

            <tr>
                <th>Sub-Total</th>
                <td><input type="number" id="total_display" readonly style="background:#f8fafc; text-align:right;"></td>
            </tr>

            <tr id="prev-bal-row">
                <th style="color:#c2410c;">Previous Balance</th>
                <td><input type="number" id="prev_bal_in_totals" readonly
                           style="background:#fff7ed; color:#c2410c; font-weight:700; text-align:right;"></td>
            </tr>

            <tr>
                <th>Discount</th>
                <td><input type="number" name="discount" id="discount" value="0" min="0" style="text-align:right;"></td>
            </tr>

            <tr id="total-row">
                <th>Total Payable</th>
                <td>
                    <input type="hidden" name="total_amount" id="total_amount">
                    <input type="hidden" name="payable_amount" id="payable_amount">
                    <div id="grand-total-display">Rs. 0</div>
                </td>
            </tr>

        </table>

        {{-- Amount in words --}}
        <table>
            <tr>
                <td width="18%">Amount in Words</td>
                <td><input type="text" name="amount_in_words" id="amount_in_words" placeholder="e.g. Two Thousand Five Hundred Only"></td>
            </tr>
            <tr>
                <td>Notes</td>
                <td><textarea name="notes" rows="2"></textarea></td>
            </tr>
        </table>

        <div class="d-flex gap-2 mt-2">
            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Voucher</button>
            <a href="{{ route('fee-vouchers.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">
                Cancel
            </a>
        </div>

    </form>

</div>
</div>

<script>

    /* ═══════════════════════════════════════════════════
     * Previous Balance — AJAX loader
     * ═══════════════════════════════════════════════════ */

    const studentSelect = document.getElementById('student_id');

    studentSelect.addEventListener('change', function () {

        const studentId = this.value;

        // Reset panel
        hidePrevBalancePanel();

        if (!studentId) return;

        // Update student_class display
        const sel = this.options[this.selectedIndex];
        document.getElementById('student_class').value = sel.dataset.className || '';

        // Load previous balance
        loadPreviousBalance(studentId);
    });

    function loadPreviousBalance(studentId) {

        const loader = document.getElementById('prev-balance-loader');
        loader.style.display = 'block';

        fetch(`/student-ledger/previous-balance?student_id=${studentId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {

            loader.style.display = 'none';

            if (data.previous_balance > 0) {
                showPrevBalancePanel(data);
            } else {
                hidePrevBalancePanel();
            }
        })
        .catch(() => {
            loader.style.display = 'none';
        });
    }

    function showPrevBalancePanel(data) {

        const panel = document.getElementById('prev-balance-panel');

        // Build breakdown table — one checkbox per voucher, all unticked
        // by default. Nothing is included until the user explicitly picks it.
        let html = '<table><thead><tr>';
        html += '<th style="width:30px; text-align:center;"><input type="checkbox" id="prevSelectAll" title="Select all"></th>';
        html += '<th>Voucher No</th><th>Month</th><th style="text-align:right">Payable</th>';
        html += '<th style="text-align:right">Paid</th><th style="text-align:right">Balance</th><th>Status</th>';
        html += '</tr></thead><tbody>';

        data.overdue_vouchers.forEach(v => {
            const statusColor = v.status === 'partial' ? '#d97706' : '#dc2626';
            html += `<tr>
                <td style="text-align:center;">
                    <input type="checkbox" class="prev-voucher-check"
                           name="selected_previous_vouchers[]" value="${v.id}"
                           data-balance="${v.balance_amount}">
                </td>
                <td><code style="font-size:11px;">${v.voucher_no}</code></td>
                <td>${v.due_date}</td>
                <td style="text-align:right">Rs. ${Number(v.payable_amount).toLocaleString('en-PK',{maximumFractionDigits:0})}</td>
                <td style="text-align:right">Rs. ${Number(v.paid_amount).toLocaleString('en-PK',{maximumFractionDigits:0})}</td>
                <td style="text-align:right; font-weight:600; color:#dc2626;">
                    Rs. ${Number(v.balance_amount).toLocaleString('en-PK',{maximumFractionDigits:0})}
                </td>
                <td><span style="background:${statusColor}22; color:${statusColor};
                          padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600;">
                    ${v.status.toUpperCase()}
                </span></td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('prev-breakdown-table').innerHTML = html;

        // "Select all" toggles every row checkbox
        document.getElementById('prevSelectAll').addEventListener('change', function () {
            document.querySelectorAll('.prev-voucher-check').forEach(cb => cb.checked = this.checked);
            calculateTotals();
        });

        // Each row checkbox recalculates the total when toggled
        document.querySelectorAll('.prev-voucher-check').forEach(cb => {
            cb.addEventListener('change', calculateTotals);
        });

        panel.classList.add('has-balance');

        // Recalculate totals (nothing selected yet, so this just resets the display)
        calculateTotals();
    }

    function hidePrevBalancePanel() {

        const panel = document.getElementById('prev-balance-panel');
        panel.classList.remove('has-balance');
        document.getElementById('prev-breakdown-table').innerHTML = '';

        calculateTotals();
    }

    /* ═══════════════════════════════════════════════════
     * Class Fee Structure map — class_id → fee_type_id → unit_rate
     * Injected from ClassFeeStructure records so JS can auto-fill
     * the correct rate per class without an extra AJAX call.
     * ═══════════════════════════════════════════════════ */
    const classFeeMap = {!! json_encode($classFeeMap) !!};

    // Helper: get the unit rate for the currently selected student's class + a fee type
    function getUnitRate(feeTypeId) {
        const classId = document.getElementById('student_id')
            ?.options[document.getElementById('student_id').selectedIndex]
            ?.dataset.class || '';
        if (!classId || !feeTypeId) return null;
        return (classFeeMap[classId] && classFeeMap[classId][feeTypeId] !== undefined)
            ? parseFloat(classFeeMap[classId][feeTypeId])
            : null;
    }

    /* ═══════════════════════════════════════════════════
     * Totals Calculation
     * ═══════════════════════════════════════════════════ */

    function calculateTotals() {

        let subtotal = 0;

        document.querySelectorAll('#feeTable tbody .amount').forEach(function (input) {
            subtotal += parseFloat(input.value) || 0;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;

        // Previous balance = sum of whichever voucher rows are ticked
        let prevBalIncluded = 0;
        document.querySelectorAll('.prev-voucher-check:checked').forEach(function (cb) {
            prevBalIncluded += parseFloat(cb.dataset.balance) || 0;
        });

        const prevBalRow    = document.getElementById('prev-bal-row');
        const prevBalInput  = document.getElementById('prev_bal_in_totals');
        const prevAmtDisp   = document.getElementById('prev-bal-amount-display');

        if (prevAmtDisp) {
            prevAmtDisp.textContent = 'Rs. ' + prevBalIncluded.toLocaleString('en-PK', { maximumFractionDigits: 0 });
        }

        if (prevBalIncluded > 0) {
            prevBalRow.style.display = '';
            prevBalInput.value = prevBalIncluded;
        } else {
            prevBalRow.style.display = 'none';
            prevBalInput.value = '';
        }

        // ── Base amount: fee items minus discount ONLY ──────────────────
        // This is what actually gets submitted in total_amount/payable_amount.
        // The previous balance is deliberately NOT folded in here — the
        // server adds the real, re-verified previous balance on top of this
        // exactly once, after checking which vouchers were actually ticked.
        // If it were baked in here too, the server's addition would double
        // it (e.g. base 6000 + prevBal 4200 submitted as 10200, then server
        // adds 4200 again = 14400).
        const baseTotal   = subtotal - discount;
        const basePayable = Math.max(0, baseTotal);

        // Combined total — for the on-screen display and "Amount in Words"
        // only, never submitted as payable_amount/total_amount.
        const displayPayable = Math.max(0, baseTotal + prevBalIncluded);

        document.getElementById('total_display').value      = subtotal;
        document.getElementById('total_amount').value       = subtotal;
        document.getElementById('payable_amount').value     = basePayable;
        document.getElementById('grand-total-display').textContent =
            'Rs. ' + displayPayable.toLocaleString('en-PK', { maximumFractionDigits: 0 });

        // Auto-fill Amount in Words — reflects the TRUE final total
        // (base + previous balance), matching what the voucher will
        // actually show once the server adds the previous balance line.
        document.getElementById('amount_in_words').value =
            displayPayable > 0 ? numberToWords(Math.round(displayPayable)) + ' Only' : '';
    }

    /* ═══════════════════════════════════════════════════
     * Number → Words (Pakistani Rupees style)
     * ═══════════════════════════════════════════════════ */

    function numberToWords(n) {
        if (n === 0) return 'Zero';

        const ones  = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                        'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                        'Seventeen','Eighteen','Nineteen'];
        const tens  = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

        function words(num) {
            if (num === 0)   return '';
            if (num < 20)    return ones[num] + ' ';
            if (num < 100)   return tens[Math.floor(num / 10)] + (num % 10 ? ' ' + ones[num % 10] : '') + ' ';
            if (num < 1000)  return ones[Math.floor(num / 100)] + ' Hundred ' + words(num % 100);
            if (num < 100000)return words(Math.floor(num / 1000)) + 'Thousand ' + words(num % 1000);
            if (num < 10000000) return words(Math.floor(num / 100000)) + 'Lakh ' + words(num % 100000);
            return words(Math.floor(num / 10000000)) + 'Crore ' + words(num % 10000000);
        }

        return words(n).trim();
    }

    /* ═══════════════════════════════════════════════════
     * Row Management
     * ═══════════════════════════════════════════════════ */

    function updateRowNumbers() {
        document.querySelectorAll('#feeTable tbody tr').forEach(function (row, i) {
            const cell = row.querySelector('.row-no');
            if (cell) cell.textContent = i + 1;
        });
    }

    function addRow() {

        const tbody    = document.querySelector('#feeTable tbody');
        const firstRow = tbody.rows[0];
        const newRow   = firstRow.cloneNode(true);

        newRow.querySelectorAll('input').forEach(function (input) {
            if (input.classList.contains('months_count')) {
                input.value = 1;
            } else {
                input.value = '';
            }
        });

        newRow.querySelectorAll('select').forEach(function (select) {
            select.selectedIndex = 0;
        });

        tbody.appendChild(newRow);
        updateRowNumbers();
        newRow.querySelector('.fee_type').focus();
    }

    function removeRow(button) {

        const tbody = document.querySelector('#feeTable tbody');

        if (tbody.rows.length > 1) {
            button.closest('tr').remove();
            updateRowNumbers();
            calculateTotals();
        }
    }

    /* ═══════════════════════════════════════════════════
     * Event Listeners
     * ═══════════════════════════════════════════════════ */

    // Fee type change → look up class-specific rate, multiply by qty, fill amount
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('fee_type')) {
            const row        = e.target.closest('tr');
            const feeTypeId  = e.target.value;
            const qtyInput   = row.querySelector('.months_count');
            const amtInput   = row.querySelector('.amount');

            // 1. Try class-specific rate from ClassFeeStructure
            // 2. Fall back to FeeType.default_amount stored in data-amount
            const classRate   = getUnitRate(feeTypeId);
            const defaultRate = parseFloat(e.target.options[e.target.selectedIndex]?.dataset.amount) || 0;
            const unitRate    = (classRate !== null) ? classRate : defaultRate;

            // Store unit rate on the row so qty changes can access it
            row.dataset.unitRate = unitRate;

            const qty = parseFloat(qtyInput?.value) || 1;
            amtInput.value = unitRate > 0 ? (unitRate * qty) : '';
            calculateTotals();
        }
    });

    // Qty (months_count) change → recompute amount = unitRate × qty
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('months_count')) {
            const row      = e.target.closest('tr');
            const unitRate = parseFloat(row.dataset.unitRate) || 0;
            const qty      = parseFloat(e.target.value) || 1;
            if (unitRate > 0) {
                row.querySelector('.amount').value = unitRate * qty;
            }
            calculateTotals();
        }
    });

    // Direct amount edits and discount also recalculate
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('amount') || e.target.id === 'discount') {
            // If user manually edits the amount, clear the stored unit rate
            // so future qty changes don't override their manual entry
            if (e.target.classList.contains('amount')) {
                const row = e.target.closest('tr');
                if (row) row.dataset.unitRate = '';
            }
            calculateTotals();
        }
    });

    // Enter on amount adds row
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.classList.contains('amount')) {
            e.preventDefault();
            addRow();
        }
    });

    // Class filter for students
    document.getElementById('class_id').addEventListener('change', function () {

        const classId = this.value;

        Array.from(studentSelect.options).forEach(function (option) {

            if (option.value === '') {
                option.hidden = false;
                return;
            }

            option.hidden = classId ? (option.dataset.class !== classId) : false;
        });

        studentSelect.value = '';
        document.getElementById('student_class').value = '';
        hidePrevBalancePanel();
    });

    // Form submit validation
    document.getElementById('voucherForm').addEventListener('submit', function (e) {

        const rows     = document.querySelectorAll('#feeTable tbody tr');
        let valid      = true;
        let hasAmount  = false;

        rows.forEach(function (row) {

            const feeType = row.querySelector('.fee_type').value;
            const amount  = row.querySelector('.amount').value;

            if (!feeType && !amount) return;

            if (!feeType) { alert('Please select fee type for all rows.'); valid = false; return; }
            if (!amount)  { alert('Amount is required for all rows.'); valid = false; return; }

            if (parseFloat(amount) > 0) hasAmount = true;
        });

        if (!hasAmount) {
            alert('Voucher cannot be empty — please add at least one fee item.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });

    /* ═══════════════════════════════════════════════════
     * Init — if student is preselected (deep-link), auto-load
     * ═══════════════════════════════════════════════════ */

    updateRowNumbers();
    calculateTotals();

    @if($preselectedStudentId)
        // Student was passed via URL — load their balance immediately
        document.addEventListener('DOMContentLoaded', function() {

            const sel = document.getElementById('student_id');

            if (sel.value) {

                const opt = sel.options[sel.selectedIndex];

                if (opt) {
                    document.getElementById('student_class').value = opt.dataset.className || '';
                }

                @if($preselectedPrevBalance > 0)
                    showPrevBalancePanel({
                        previous_balance: {{ $preselectedPrevBalance }},
                        overdue_vouchers: {!! json_encode($preselectedOverdue) !!}
                    });
                @endif
            }
        });
    @endif

</script>

@endsection 
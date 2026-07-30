@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .pe-wrap {
        max-width: 620px;
        margin: 0 auto;
    }
    .pe-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 14px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .pe-header {
        background: #1e293b;
        color: #fff;
        padding: 20px 26px;
    }
    .pe-header h2 { margin: 0; font-size: 1.2rem; font-weight: 700; }
    .pe-header .sub { font-size: .8rem; color: #94a3b8; margin-top: 3px; }
    .pe-body { padding: 24px 26px; }

    .pe-meta {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: .82rem;
        margin-bottom: 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 16px;
    }
    .pe-meta .lbl { color: #94a3b8; font-weight: 600; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; }
    .pe-meta .val { color: #1e293b; font-weight: 700; }

    .pe-field { margin-bottom: 16px; }
    .pe-field label { display: block; font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: 5px; }
    .pe-field input,
    .pe-field select,
    .pe-field textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        font-size: .85rem;
    }
    .pe-field input:focus,
    .pe-field select:focus,
    .pe-field textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    }

    .pe-actions { display: flex; gap: 10px; margin-top: 22px; }
    .btn-pe-primary {
        background: #1d4ed8; color: #fff; border: none;
        padding: 10px 22px; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer;
    }
    .btn-pe-cancel {
        background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;
        padding: 10px 22px; border-radius: 8px; font-size: .85rem; font-weight: 600;
        text-decoration: none; display: inline-flex; align-items: center;
    }

    .warn-box {
        background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
        font-size: .78rem; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px;
    }
</style>

<div class="pe-wrap mt-4">
    <div class="pe-card">

        <div class="pe-header">
            <h2><i class="fas fa-pen me-2"></i>Edit Payment</h2>
            <div class="sub">Receipt: {{ $payment->receipt_no }}</div>
        </div>

        <div class="pe-body">

            @if($methodOnlyMode)
            <div class="warn-box" style="background:#fef3c7;border-color:#fbbf24;color:#92400e;">
                <i class="fas fa-lock me-1"></i>
                <strong>Paid Voucher — Method Correction Only.</strong>
                Amount and date are locked. You can only change the payment method, reference number, or notes.
            </div>
            @else
            <div class="warn-box">
                <i class="fas fa-info-circle me-1"></i>
                Updating this payment will automatically recalculate the voucher's paid amount, balance, and status.
            </div>
            @endif

            <div class="pe-meta">
                <div>
                    <div class="lbl">Voucher No</div>
                    <div class="val">{{ $payment->voucher->voucher_no ?? ('#' . $payment->voucher_id) }}</div>
                </div>
                <div>
                    <div class="lbl">Student</div>
                    <div class="val">{{ $payment->student->student_name ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="lbl">Payable Amount</div>
                    <div class="val">Rs. {{ number_format($payment->voucher->payable_amount ?? 0, 0) }}</div>
                </div>
                <div>
                    <div class="lbl">Current Voucher Status</div>
                    <div class="val text-capitalize">{{ $payment->voucher->status ?? 'N/A' }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('fee-payments.update', $payment->id) }}">
                @csrf
                @method('PUT')

                <div class="pe-field">
                    <label>Amount Paid (Rs.) @if($methodOnlyMode)<span style="font-size:.7rem;color:#94a3b8;font-weight:400;margin-left:6px;"><i class="fas fa-lock"></i> locked</span>@endif</label>
                    <input type="number" name="amount_paid" step="0.01" min="1"
                           value="{{ old('amount_paid', $payment->amount_paid) }}"
                           {{ $methodOnlyMode ? 'readonly' : 'required' }}
                           style="{{ $methodOnlyMode ? 'background:#f1f5f9;color:#64748b;cursor:not-allowed;' : '' }}">
                </div>

                <div class="pe-field">
                    <label>Payment Date @if($methodOnlyMode)<span style="font-size:.7rem;color:#94a3b8;font-weight:400;margin-left:6px;"><i class="fas fa-lock"></i> locked</span>@endif</label>
                    <input type="text" name="payment_date" id="payment_date"
                           value="{{ old('payment_date', \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d')) }}"
                           autocomplete="off"
                           {{ $methodOnlyMode ? 'readonly' : 'required' }}
                           style="{{ $methodOnlyMode ? 'background:#f1f5f9;color:#64748b;cursor:not-allowed;' : '' }}">
                    @unless($methodOnlyMode)
                    <small style="color:#94a3b8;display:block;margin-top:4px;">Cannot be a future date.</small>
                    @endunless
                </div>

                <div class="pe-field">
                    <label>Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
    <option value="">-- Select Payment Method --</option>
    @foreach($paymentMethods as $method)
        <option value="{{ $method['key'] }}"
            {{ old('payment_method', $payment->payment_method ?? '') == $method['key'] ? 'selected' : '' }}>
            {{ $method['label'] }}
        </option>
    @endforeach
</select>
                </div>

                <div class="pe-field">
                    <label>Reference No. (optional)</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $payment->reference_no) }}">
                </div>

                <div class="pe-field">
                    <label>Notes (optional)</label>
                    <textarea name="notes" rows="3">{{ old('notes', $payment->notes) }}</textarea>
                </div>

                <div class="pe-actions">
                    <button type="submit" class="btn-pe-primary">
                        <i class="fas fa-check me-1"></i>
                        @if($methodOnlyMode) Save Method Change @else Update Payment @endif
                    </button>
                    <a href="{{ route('fee-vouchers.index') }}" class="btn-pe-cancel">Cancel</a>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    @if(!$methodOnlyMode)
    flatpickr("#payment_date", {
        dateFormat: "Y-m-d",     // actual value submitted to the server
        altInput: true,
        altFormat: "d-M-Y",      // what the user sees, e.g. 12-Apr-2026
        maxDate: "today",        // blocks any future date
        allowInput: false
    });
    @endif
</script>

@endsection
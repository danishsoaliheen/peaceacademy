@extends('layouts.dashboard')

@section('content')

<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="fas fa-edit me-2" style="opacity:.8;"></i>Edit Expense</h2>
            <p>Update expense · <span style="opacity:.7;">{{ $expense->expense_no }}</span></p>
        </div>
        <a href="{{ route('expenses.index') }}" class="btn-hero-ghost"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-triangle me-2"></i><strong>Please fix:</strong>
    <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('expenses.update', $expense) }}">
@csrf @method('PUT')
<div class="row g-4">

    <div class="col-lg-8">
        <div class="section-card card">
            <div class="card-header">
                <span class="s-icon" style="background:#dc2626;"><i class="fas fa-receipt"></i></span>
                <h6>Expense Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" id="mainCat" class="form-select" required onchange="loadSubCats()">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $cat => $subs)
                            <option value="{{ $cat }}" {{ old('category',$expense->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sub-Category</label>
                        <select name="sub_category" id="subCat" class="form-select">
                            <option value="">— Select Sub-Category —</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control"
                               value="{{ old('description',$expense->description) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Amount (PKR) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="1" min="1"
                               value="{{ old('amount',$expense->amount) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control"
                               value="{{ old('expense_date',$expense->expense_date->format('Y-m-d')) }}" required>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="section-card card sticky-side">
            <div class="card-header">
                <span class="s-icon" style="background:#0f172a;"><i class="fas fa-money-bill-wave"></i></span>
                <h6>Payment Info</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            @foreach(['Cash','Bank Transfer','EasyPaisa','JazzCash','Cheque'] as $m)
                            <option value="{{ $m }}" {{ old('payment_method',$expense->payment_method) == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Paid To</label>
                        <input type="text" name="paid_to" class="form-control" value="{{ old('paid_to',$expense->paid_to) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Reference / Cheque No.</label>
                        <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no',$expense->reference_no) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Recorded By</label>
                        <input type="text" name="recorded_by" class="form-control" value="{{ old('recorded_by',$expense->recorded_by) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes',$expense->notes) }}</textarea>
                    </div>

                    <div class="col-12 pt-1">
                        <button type="submit" class="btn-save w-100">
                            <i class="fas fa-save"></i> Update Expense
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
</form>

@push('scripts')
<script>
const CATS    = @json($categories);
const savedSub = "{{ old('sub_category', $expense->sub_category) }}";

function loadSubCats() {
    const cat = document.getElementById('mainCat').value;
    const sel = document.getElementById('subCat');
    sel.innerHTML = '<option value="">— Select Sub-Category —</option>';
    if (CATS[cat]) {
        CATS[cat].forEach(s => {
            const o = document.createElement('option');
            o.value = s; o.textContent = s;
            if (s === savedSub) o.selected = true;
            sel.appendChild(o);
        });
    }
}
document.addEventListener('DOMContentLoaded', loadSubCats);
</script>
@endpush

@endsection

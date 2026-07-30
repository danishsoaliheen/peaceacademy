{{-- resources/views/settings/payment-methods.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Payment Method Settings')

@section('content')
<div class="page-hero mb-4">
    <div class="hero-content">
        <div class="hero-icon"><i class="fas fa-credit-card"></i></div>
        <div>
            <h1 class="hero-title">Payment Methods</h1>
            <p class="hero-subtitle">Enable, disable, rename, or reorder payment methods used across fee collection</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="section-card">
    <div class="card-header-pa d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Manage Payment Methods</h5>
        <small class="text-muted"><i class="fas fa-grip-vertical me-1"></i>Drag rows to reorder</small>
    </div>
    <div class="card-body p-0">
        <form action="{{ route('settings.payment-methods.update') }}" method="POST" id="paymentMethodsForm">
            @csrf

            <table class="table pa-table mb-0" id="methodsTable">
                <thead>
                    <tr>
                        <th width="40"></th>
                        <th>Payment Method</th>
                        <th>Display Label</th>
                        <th width="100" class="text-center">Enabled</th>
                    </tr>
                </thead>
                <tbody id="sortableBody">
                    @foreach($methods as $i => $method)
                    <tr class="method-row" data-key="{{ $method['key'] }}">
                        <td class="drag-handle text-center" style="cursor:grab; color:#94a3b8;">
                            <i class="fas fa-grip-vertical"></i>
                        </td>
                        <td>
                            <input type="hidden" name="methods[{{ $i }}][key]"  value="{{ $method['key'] }}">
                            <input type="hidden" name="methods[{{ $i }}][icon]" value="{{ $method['icon'] }}">
                            <i class="fas {{ $method['icon'] }} me-2 text-primary"></i>
                            <strong>{{ $method['key'] }}</strong>
                        </td>
                        <td>
                            <input type="text"
                                   name="methods[{{ $i }}][label]"
                                   class="form-control form-control-sm"
                                   value="{{ $method['label'] }}"
                                   style="max-width:260px;">
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="methods[{{ $i }}][enabled]"
                                       value="1"
                                       {{ $method['enabled'] ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-3 border-top d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- SortableJS from CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    const tbody = document.getElementById('sortableBody');

    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function () {
            // Re-index all name attributes after drag
            const rows = tbody.querySelectorAll('tr.method-row');
            rows.forEach((row, idx) => {
                row.querySelectorAll('input, select').forEach(el => {
                    el.name = el.name.replace(/methods\[\d+\]/, `methods[${idx}]`);
                });
            });
        }
    });
</script>
@endpush
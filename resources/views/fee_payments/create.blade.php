@extends('layouts.dashboard')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>

    .payment-box{

        background:white;
        padding:20px;
        border-radius:8px;
        box-shadow:0 0 10px rgba(0,0,0,0.1);
    }

    table{

        width:100%;
        border-collapse:collapse;
    }

    td{

        padding:10px;
        border:1px solid #ddd;
    }

    input,
    select,
    textarea{

        width:100%;
        padding:8px;
    }

    .btn{

        background:#0d6efd;
        color:white;
        padding:10px 20px;
        border:none;
        border-radius:5px;
    }

</style>

<div class="container mt-4">

    <div class="payment-box">

        <h2>
            Receive Fee Payment
        </h2>

        <hr>

        <form method="POST"
              action="{{ route('fee-payments.store') }}">

            @csrf

            <input type="hidden"
                   name="voucher_id"
                   value="{{ $voucher->id }}">

            <input type="hidden"
                   name="student_id"
                   value="{{ $voucher->student_id }}">

            <table>

                <tr>

                    <td width="25%">
                        Voucher No
                    </td>

                    <td>
                        {{ $voucher->voucher_no }}
                    </td>

                </tr>

                <tr>

                    <td>
                        Student Name
                    </td>

                    <td>

                        {{ strtoupper($voucher->student->student_name ?? '') }}

                    </td>

                </tr>

                <tr>

                    <td>
                        Voucher Amount
                    </td>

                    <td>

                        {{ number_format($voucher->payable_amount,0) }}

                    </td>

                </tr>

                <tr>

                    <td>
                        Already Paid
                    </td>

                    <td>

                        {{ number_format($voucher->paid_amount,0) }}

                    </td>

                </tr>

                <tr>

                    <td>
                        Balance Amount
                    </td>

                    <td>

                        {{ number_format($voucher->balance_amount,0) }}

                    </td>

                </tr>

                <tr>

                    <td>
                        Receive Amount
                    </td>

                    <td>

                        <input type="number"
                               name="amount_paid"
                               id="amount_paid"
                               required
                               min="1"
                               max="{{ $voucher->balance_amount }}"
                               step="0.01">

                        <small style="color:#6c757d;display:block;margin-top:4px;">
                            Balance due: Rs. {{ number_format($voucher->balance_amount, 0) }}
                        </small>

                    </td>

                </tr>

                <tr>

                    <td>
                        Payment Date
                    </td>

                    <td>

                        <input type="text"
                               name="payment_date"
                               id="payment_date"
                               value="{{ date('Y-m-d') }}"
                               autocomplete="off"
                               required>

                        <small style="color:#6c757d;display:block;margin-top:4px;">
                            Cannot be a future date.
                        </small>

                    </td>

                </tr>

                <tr>

                    <td>
                        Payment Method
                    </td>

                    <td>

                        <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
    <option value="">-- Select Payment Method --</option>
    @foreach($paymentMethods as $method)
        <option value="{{ $method['key'] }}"
            {{ old('payment_method', $payment->payment_method ?? '') == $method['key'] ? 'selected' : '' }}>
            {{ $method['label'] }}
        </option>
    @endforeach
</select>
                    </td>

                </tr>

                <tr>

                    <td>
                        Reference No
                    </td>

                    <td>

                        <input type="text"
                               name="reference_no">

                    </td>

                </tr>

                <tr>

                    <td>
                        Notes
                    </td>

                    <td>

                        <textarea name="notes"
                                  rows="3"></textarea>

                    </td>

                </tr>

            </table>

            <br>

            <button type="submit"
                    class="btn">

                Save Payment

            </button>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#payment_date", {
        dateFormat: "Y-m-d",     // actual value submitted to the server
        altInput: true,
        altFormat: "d-M-Y",      // what the user sees, e.g. 12-Apr-2026
        maxDate: "today",        // blocks any future date, past dates still allowed
        defaultDate: "today",
        allowInput: false
    });
</script>

@endsection
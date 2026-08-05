<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $payment->receipt_no ?? '' }} — {{ strtoupper($payment->student->student_name ?? '') }}</title>
    <style>

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background: #e5e7eb;
            color: #111;
            padding: 24px;
        }

        /* Screen controls */
        .screen-bar {
            width: 780px;
            margin: 0 auto 16px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn-print {
            background: #1d4ed8;
            color: #fff;
            border: none;
            padding: 9px 22px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .btn-print:hover { background: #1e40af; }
        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-edit {
            background: #7c3aed;
            color: #fff;
            border: none;
            padding: 9px 22px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .btn-edit:hover { background: #6d28d9; }

        /* Receipt wrapper */
        .receipt {
            width: 780px;
            margin: 0 auto;
            background: #fff;
            border: 1.5px solid #334155;
            border-radius: 6px;
            overflow: hidden;
        }

        /* Header */
        .r-header {
            background: #1e293b;
            color: #fff;
            text-align: center;
            padding: 18px 20px 14px;
        }
        .r-header .school-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: .04em;
        }
        .r-header .school-sub {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 3px;
        }
        .r-header .receipt-title {
            margin-top: 10px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #e2e8f0;
            border-top: 1px solid #334155;
            padding-top: 8px;
        }

        /* Body */
        .r-body { padding: 18px 20px; }

        /* Info grid */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .info-grid td {
            padding: 5px 8px;
            font-size: 12.5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .info-grid td.lbl {
            font-weight: 700;
            color: #475569;
            background: #f8fafc;
            width: 18%;
            white-space: nowrap;
        }
        .info-grid td.val { width: 32%; }

        /* Fee items table */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #475569;
            background: #f1f5f9;
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            border-bottom: none;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .items-table thead th {
            background: #1e293b;
            color: #e2e8f0;
            padding: 7px 8px;
            font-size: 11px;
            font-weight: 700;
            text-align: left;
            letter-spacing: .05em;
            border: none;
        }
        .items-table tbody td {
            padding: 7px 8px;
            border-bottom: 1px solid #f1f5f9;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }
        .items-table tbody tr:nth-child(even) td { background: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'Courier New', monospace; }

        /* Amount highlight box */
        .amount-box {
            margin-top: 16px;
            padding: 14px 20px;
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 6px;
            text-align: center;
        }
        .amount-box .amount-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #166534;
        }
        .amount-box .amount-value {
            font-size: 26px;
            font-weight: 800;
            color: #15803d;
            font-family: 'Courier New', monospace;
            margin-top: 4px;
        }

        /* Amount in words */
        .amount-words {
            margin-top: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 12.5px;
            background: #f8fafc;
        }
        .amount-words strong { color: #475569; }

        /* Notes */
        .notes-box {
            margin-top: 14px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            background: #fffbeb;
            font-size: 12px;
        }
        .notes-box strong { color: #92400e; }

        /* Footer */
        .r-footer {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 11px;
            color: #94a3b8;
        }
        .r-footer .sig-block {
            text-align: center;
            width: 180px;
        }
        .r-footer .sig-line {
            border-top: 1px solid #334155;
            margin-bottom: 4px;
        }
        .r-footer .sig-label { font-size: 11px; color: #475569; font-weight: 600; }

        /* Print */
        @media print {
            body { background: #fff; padding: 0; }
            .screen-bar .btn-edit { display: none; }
            .receipt {
                width: 100%;
                border: none;
                border-radius: 0;
                box-shadow: none;
            }
            @page { size: A4; margin: 10mm; }
        }

    </style>
</head>
<body>

{{-- Screen controls --}}
<div class="screen-bar">
    <button class="btn-print" onclick="window.print()">
        Print / Save PDF
    </button>
    <a href="{{ route('fee-payments.edit', $payment->id) }}" class="btn-edit">
        <i class="fas fa-pen-to-square"></i> Edit Receipt
    </a>
    <a href="{{ url()->previous() }}" class="btn-back">Back</a>
    <span style="margin-left:auto;font-size:12px;color:#64748b;">
        Receipt: <strong>{{ $payment->receipt_no ?? ('PAY-' . $payment->id) }}</strong>
    </span>
</div>

{{-- Receipt card --}}
<div class="receipt">

    {{-- Header --}}
    <div class="r-header">
        <div class="school-name">PEACE ACADEMY</div>
        <div class="school-sub">Fee Payment Receipt</div>
        <div class="receipt-title">Payment Receipt</div>
    </div>

    <div class="r-body">

        {{-- Info grid --}}
        <table class="info-grid">
            <tr>
                <td class="lbl">Receipt No</td>
                <td class="val mono" style="font-weight:700;color:#1d4ed8">
                    {{ $payment->receipt_no ?? ('PAY-' . $payment->id) }}
                </td>
                <td class="lbl">Payment Date</td>
                <td class="val">
                    {{ strtoupper(\Carbon\Carbon::parse($payment->payment_date)->format('d-M-Y')) }}
                </td>
            </tr>
            <tr>
                <td class="lbl">Student Name</td>
                <td class="val" style="font-weight:700">
                    {{ strtoupper($payment->student->student_name ?? '') }}
                </td>
                <td class="lbl">GR / Adm No</td>
                <td class="val mono">
                    {{ strtoupper($payment->student->admission_no ?? '') }}
                </td>
            </tr>
            <tr>
                <td class="lbl">Voucher No</td>
                <td class="val mono" style="font-weight:700">
                    <a href="{{ route('fee-vouchers.print', $payment->voucher_id) }}"
                       target="_blank"
                       style="color:#1d4ed8;text-decoration:underline dotted;cursor:pointer;"
                       title="Click to view full voucher">
                        {{ $payment->voucher->voucher_no ?? ('#' . $payment->voucher_id) }}
                        <i class="fas fa-external-link-alt" style="font-size:9px;margin-left:3px;color:#7c3aed;"></i>
                    </a>
                </td>
                <td class="lbl">Payment Method</td>
                <td class="val">
                    {{ ucfirst($payment->payment_method ?? 'Cash') }}
                </td>
            </tr>
            <tr>
                <td class="lbl">Received By</td>
                <td class="val">{{ $payment->received_by ?? 'Admin' }}</td>
                <td class="lbl">Reference No</td>
                <td class="val mono">{{ $payment->reference_no ?? 'N/A' }}</td>
            </tr>
        </table>

        {{-- Fee items from the voucher --}}
        @if($payment->voucher && $payment->voucher->items && $payment->voucher->items->count() > 0)

        <div class="section-title">Fee Items (Voucher {{ $payment->voucher->voucher_no ?? '' }})</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="30%">Fee Type</th>
                    <th width="35%">Description</th>
                    <th width="15%">Month</th>
                    <th width="15%" class="text-right">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->voucher->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->feeType->name ?? '—' }}</td>
                    <td style="color:#475569">{{ $item->description ?: '—' }}</td>
                    <td>
                        @if($item->month)
                            {{ strtoupper(\Carbon\Carbon::parse($item->month)->format('M Y')) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right mono">{{ number_format($item->amount, 0) }}</td>
                </tr>
                @endforeach
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;font-weight:700;padding:7px 8px;
                        border:1px solid #e2e8f0;border-top:2px solid #334155;background:#f8fafc;">
                        Voucher Total
                    </td>
                    <td class="text-right mono" style="font-weight:800;padding:7px 8px;
                        border:1px solid #e2e8f0;border-top:2px solid #334155;background:#f8fafc;">
                        Rs. {{ number_format($payment->voucher->payable_amount, 0) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        @endif

        {{-- Amount paid highlight --}}
        <div class="amount-box">
            <div class="amount-label">Amount Received</div>
            <div class="amount-value">Rs. {{ number_format($payment->amount_paid, 0) }}</div>
        </div>

        {{-- Amount in words (for this specific payment) --}}
        <div class="amount-words">
            <strong>Amount in Words:</strong>
            {{ strtoupper(App\Helpers\NumberToWords::convert($payment->amount_paid) . ' Rupees Only') }}
        </div>

        {{-- Notes --}}
        @if($payment->notes)
        <div class="notes-box">
            <strong>Notes:</strong> {{ $payment->notes }}
        </div>
        @endif

        {{-- Footer --}}
        <div class="r-footer">
            <div style="font-size:11px;color:#94a3b8;line-height:1.6">
                <div>Payment ID: #{{ $payment->id }} | Receipt: {{ $payment->receipt_no ?? 'N/A' }}</div>
                <div>Printed: {{ now()->setTimezone('Asia/Karachi')->format('d-M-Y h:i A') }}</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Authorized Signatory</div>
            </div>
        </div>

    </div>
</div>


    @if($download)
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    window.onload = function () {
        const element  = document.querySelector('.receipt');
        const filename = 'Receipt-{{ $payment->receipt_no ?? $payment->id }}.pdf';

        html2pdf().set({
            margin: 10,
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        }).from(element).save();
    };
</script>
@else
<script>
    window.onload = function () { window.print(); };
</script>
@endif

</body>
</html>
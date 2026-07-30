<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #999; padding: 6px 10px; font-size: 12px; }
    th { background: #1e293b; color: #fff; font-weight: bold; text-align: left; }
    .r { text-align: right; mso-number-format:"#,##0"; }
    .title { font-size: 16px; font-weight: bold; }
    .sub { font-size: 11px; color: #555; }
    .overdue { color: #c0392b; font-weight: bold; }
    .clear { color: #16a34a; }
    tfoot td { font-weight: bold; background: #f1f5f9; }
</style>
</head>
<body>

    <div class="title">Peace Academy — Student Balance Sheet</div>
    <div class="sub">
        Generated: {{ now()->setTimezone('Asia/Karachi')->format('d-M-Y h:i A') }}
        @if($className) &middot; Class: {{ $className }} @endif
        &middot; {{ $students->count() }} student(s)
    </div>
    <br>

    <table>
        <thead>
            <tr>
                <th>Sr#</th>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Father Name</th>
                <th>Class</th>
                <th>Last Voucher No</th>
                <th class="r">Outstanding Balance (Rs.)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $student)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $student->admission_no }}</td>
                <td>{{ strtoupper($student->student_name) }}</td>
                <td>{{ strtoupper($student->father_name ?? '') }}</td>
                <td>{{ $student->activeEnrollment?->class?->class_name ?? '' }}</td>
                <td>{{ $student->last_voucher->voucher_no ?? '' }}</td>
                <td class="r">{{ number_format($student->outstanding_balance, 0) }}</td>
                <td>
                    @if($student->outstanding_balance > 0)
                        @if($student->overdue_count > 0)
                            <span class="overdue">Overdue</span>
                        @else
                            Outstanding
                        @endif
                    @else
                        <span class="clear">Clear</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right;">Total Outstanding:</td>
                <td class="r">{{ number_format($students->sum('outstanding_balance'), 0) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<style>

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #999;
        padding: 6px 10px;
        font-size: 12px;
    }

    th {
        background: #1e293b;
        color: #fff;
        font-weight: bold;
        text-align: left;
    }

    .r {
        text-align: right;
        mso-number-format: "#,##0";
    }

    .title {
        font-size: 16px;
        font-weight: bold;
    }

    .sub {
        font-size: 11px;
        color: #555;
    }

    .filter {
        font-size: 12px;
        font-weight: bold;
    }

    .inactive {
        color: #777;
        font-style: italic;
    }

    tfoot td {
        font-weight: bold;
        background: #f1f5f9;
    }

</style>

</head>


<body>


    <!-- ===================================================== -->
    <!-- Report Title -->
    <!-- ===================================================== -->

    <div class="title">

        Peace Academy — Payment History

    </div>


    <!-- ===================================================== -->
    <!-- Report Information -->
    <!-- ===================================================== -->

    <div class="sub">

        Generated:

        {{ now()
            ->setTimezone('Asia/Karachi')
            ->format('d-M-Y h:i A') }}

        &middot;

        Period:

        {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}

        –

        {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}

        &middot;

        {{ $payments->count() }} payment(s)

    </div>


    <br>


    <!-- ===================================================== -->
    <!-- Selected Filters -->
    <!-- ===================================================== -->

    @if($selectedFamilyCode)

        <div class="filter">

            Family Code:

            {{ $selectedFamilyCode }}

            — Includes active and inactive students

        </div>

    @endif


    @if($selectedStudent)

        <div class="filter">

            Student:

            {{ strtoupper($selectedStudent->student_name) }}

            @if(!$selectedStudent->is_active)

                <span class="inactive">
                    (Inactive)
                </span>

            @endif

            @if($selectedStudent->family_code)

                &middot;

                Family Code:

                {{ $selectedStudent->family_code }}

            @endif

        </div>

    @endif


    @if($selectedFamilyCode || $selectedStudent)

        <br>

    @endif


    <!-- ===================================================== -->
    <!-- Payment Table -->
    <!-- ===================================================== -->

    <table>

        <thead>

            <tr>

                <th>
                    Sr#
                </th>

                <th>
                    Receipt No
                </th>

                <th>
                    Student
                </th>

                <th>
                    Family Code
                </th>

                <th>
                    Admission No
                </th>

                <th>
                    Voucher No
                </th>

                <th class="r">
                    Amount Paid (Rs.)
                </th>

                <th>
                    Payment Date
                </th>

                <th>
                    Method
                </th>

                <th>
                    Received By
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach($payments as $i => $payment)

                <tr>

                    <td>
                        {{ $i + 1 }}
                    </td>


                    <td>
                        {{ $payment->receipt_no }}
                    </td>


                    <td>

                        {{ strtoupper(
                            $payment->student->student_name ?? ''
                        ) }}

                        @if(
                            $payment->student &&
                            !$payment->student->is_active
                        )

                            <span class="inactive">
                                (Inactive)
                            </span>

                        @endif

                    </td>


                    <td>

                        {{ $payment->student->family_code ?? '' }}

                    </td>


                    <td>

                        {{ $payment->student->admission_no ?? '' }}

                    </td>


                    <td>

                        {{ $payment->voucher->voucher_no ?? '' }}

                    </td>


                    <td class="r">

                        {{ number_format(
                            $payment->amount_paid,
                            0
                        ) }}

                    </td>


                    <td>

                        {{ \Carbon\Carbon::parse(
                            $payment->payment_date
                        )->format('d-M-Y') }}

                    </td>


                    <td>

                        {{ $payment->payment_method }}

                    </td>


                    <td>

                        {{ $payment->received_by }}

                    </td>

                </tr>

            @endforeach

        </tbody>


        <!-- ================================================= -->
        <!-- Total -->
        <!-- ================================================= -->

        <tfoot>

            <tr>

                <td colspan="6"
                    style="text-align:right;">

                    Total Received:

                </td>

                <td class="r">

                    {{ number_format(
                        $totalReceived,
                        0
                    ) }}

                </td>

                <td colspan="3"></td>

            </tr>

        </tfoot>

    </table>


</body>

</html>
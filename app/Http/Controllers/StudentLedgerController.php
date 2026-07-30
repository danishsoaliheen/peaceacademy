<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\FeeVoucher;
use App\Models\FeePayment;
use App\Models\PaEnrollment;
use App\Models\PaClass;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentLedgerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Shared filtered query (used by both index() and exportBalanceSheet())
    |--------------------------------------------------------------------------
    |
    | Attaches three computed columns to each student via correlated
    | subqueries, so filtering/sorting/exporting all stay consistent:
    |   - outstanding_balance : sum of unpaid/partial balances (matches
    |                           FeeVoucher::scopeOutstanding() exactly)
    |   - overdue_count       : how many of those are past their due date
    |   - last_voucher_id     : id of the most recently created voucher
    |--------------------------------------------------------------------------
    */

    private function filteredStudentsQuery(Request $request)
    {
        $query           = $request->search;
        $classFilter     = $request->class_id;
        $outstandingOnly = $request->boolean('outstanding_only');
        $today           = Carbon::now()->toDateString();

        $studentsQuery = Student::query()
            ->with(['activeEnrollment.class'])
            ->where('students.is_active', 1)
            ->select('students.*')
            ->selectSub(function ($q) {
                $q->from('fee_vouchers')
                  ->selectRaw('COALESCE(SUM(balance_amount), 0)')
                  ->whereColumn('fee_vouchers.student_id', 'students.id')
                  ->whereIn('status', ['unpaid', 'partial'])
                  ->where('balance_amount', '>', 0);
            }, 'outstanding_balance')
            ->selectSub(function ($q) use ($today) {
                $q->from('fee_vouchers')
                  ->selectRaw('COUNT(*)')
                  ->whereColumn('fee_vouchers.student_id', 'students.id')
                  ->whereIn('status', ['unpaid', 'partial'])
                  ->where('balance_amount', '>', 0)
                  ->where('due_date', '<', $today);
            }, 'overdue_count')
            ->selectSub(function ($q) {
                $q->from('fee_vouchers as fv_last')
                  ->select('fv_last.id')
                  ->whereColumn('fv_last.student_id', 'students.id')
                  ->orderByDesc('fv_last.created_at')
                  ->limit(1);
            }, 'last_voucher_id')
            ->when($query, function ($q) use ($query) {
                $q->where('students.student_name', 'like', "%{$query}%");
            })
            ->when($classFilter, function ($q) use ($classFilter) {
                $q->whereHas('activeEnrollment', function ($e) use ($classFilter) {
                    $e->where('class_id', $classFilter);
                });
            });

        if ($outstandingOnly) {
            $studentsQuery->havingRaw('outstanding_balance > 0');
        }

        return $studentsQuery;
    }

    /**
     * Attach the actual FeeVoucher model (voucher_no etc.) for the
     * last_voucher_id computed on each student in a collection.
     */
    private function attachLastVouchers($students)
    {
        $lastVoucherIds = $students->pluck('last_voucher_id')->filter()->values();

        $lastVouchers = FeeVoucher::whereIn('id', $lastVoucherIds)->get()->keyBy('id');

        foreach ($students as $student) {
            $student->last_voucher = $student->last_voucher_id
                ? $lastVouchers->get($student->last_voucher_id)
                : null;
        }

        return $students;
    }

    /*
    |--------------------------------------------------------------------------
    | Student Search / Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query           = $request->search;
        $classFilter     = $request->class_id;
        $outstandingOnly = $request->boolean('outstanding_only');
        $sort            = $request->sort ?? 'name';

        $studentsQuery = $this->filteredStudentsQuery($request);

        /*
        |----------------------------------------------------------------------
        | Summary stats over the FULL filtered set (before pagination), so
        | the numbers on the summary cards always reflect the filters
        | currently applied, not just the current page of 25.
        |----------------------------------------------------------------------
        */
        $allMatched = (clone $studentsQuery)->get();

        $totalStudentsMatched = $allMatched->count();
        $totalOutstandingAll  = $allMatched->sum('outstanding_balance');
        $studentsWithDues     = $allMatched->where('outstanding_balance', '>', 0)->count();
        $studentsOverdue      = $allMatched->where('overdue_count', '>', 0)->count();

        if ($sort === 'outstanding') {
            $studentsQuery->orderByDesc('outstanding_balance');
        } else {
            $studentsQuery->orderBy('students.student_name');
        }

        $students = $studentsQuery->paginate(25)->withQueryString();

        $this->attachLastVouchers($students);

        // All student names for autocomplete (lightweight — name + id only)
        $allNames = Student::where('is_active', 1)
            ->orderBy('student_name')
            ->pluck('student_name');

        $classes = PaClass::orderBy('class_order')->get();

        return view('student_ledger.index', compact(
            'students',
            'query',
            'allNames',
            'classes',
            'classFilter',
            'outstandingOnly',
            'sort',
            'totalStudentsMatched',
            'totalOutstandingAll',
            'studentsWithDues',
            'studentsOverdue'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Export Balance Sheet (real .xlsx via PhpSpreadsheet)
    |--------------------------------------------------------------------------
    |
    | Reuses the exact same filters as index() (search / class / outstanding
    | only / sort) so "Export" always matches what's currently on screen.
    | Requires: composer require phpoffice/phpspreadsheet
    |--------------------------------------------------------------------------
    */

    public function exportBalanceSheet(Request $request)
    {
        $sort = $request->sort ?? 'name';

        $studentsQuery = $this->filteredStudentsQuery($request);

        if ($sort === 'outstanding') {
            $studentsQuery->orderByDesc('outstanding_balance');
        } else {
            $studentsQuery->orderBy('students.student_name');
        }

        $students = $studentsQuery->get();

        $this->attachLastVouchers($students);

        $classFilter = $request->class_id;
        $className   = $classFilter ? PaClass::find($classFilter)?->class_name : null;

        /*
        |----------------------------------------------------------------------
        | Build the workbook
        |----------------------------------------------------------------------
        */

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Balance Sheet');

        // Title
        $sheet->setCellValue('A1', 'Peace Academy — Student Balance Sheet');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Subtitle
        $subtitle = 'Generated: ' . now()->setTimezone('Asia/Karachi')->format('d-M-Y h:i A');
        if ($className) {
            $subtitle .= '   |   Class: ' . $className;
        }
        $subtitle .= '   |   ' . $students->count() . ' student(s)';

        $sheet->setCellValue('A2', $subtitle);
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('64748B');

        // Header row
        $headerRow = 4;
        $headers   = ['Sr#', 'Admission No', 'Student Name', 'Father Name', 'Class', 'Last Voucher No', 'Outstanding Balance (Rs.)', 'Status'];

        foreach ($headers as $i => $label) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue($col . $headerRow, $label);
        }

        $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E293B');

        // Data rows
        $row = $headerRow + 1;
        $sr  = 1;

        foreach ($students as $student) {
            $status = 'Clear';
            if ($student->outstanding_balance > 0) {
                $status = $student->overdue_count > 0 ? 'Overdue' : 'Outstanding';
            }

            $sheet->setCellValue('A' . $row, $sr);
            $sheet->setCellValue('B' . $row, $student->admission_no);
            $sheet->setCellValue('C' . $row, strtoupper($student->student_name));
            $sheet->setCellValue('D' . $row, strtoupper($student->father_name ?? ''));
            $sheet->setCellValue('E' . $row, $student->activeEnrollment?->class?->class_name ?? '');
            $sheet->setCellValue('F' . $row, $student->last_voucher->voucher_no ?? '');
            $sheet->setCellValue('G' . $row, (float) $student->outstanding_balance);
            $sheet->setCellValue('H' . $row, $status);

            if ($status === 'Overdue') {
                $sheet->getStyle('H' . $row)->getFont()->setBold(true);
                $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('C0392B');
            } elseif ($status === 'Clear') {
                $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('16A34A');
            }

            $sr++;
            $row++;
        }

        // Total row
        $totalRow = $row;
        $sheet->setCellValue('F' . $totalRow, 'Total Outstanding:');
        $sheet->getStyle('F' . $totalRow)->getFont()->setBold(true);
        $sheet->getStyle('F' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('G' . $totalRow, (float) $students->sum('outstanding_balance'));
        $sheet->getStyle('G' . $totalRow)->getFont()->setBold(true);

        // Number format + auto-width + freeze header
        $sheet->getStyle('G' . ($headerRow + 1) . ':G' . $totalRow)
            ->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A' . ($headerRow + 1));

        /*
        |----------------------------------------------------------------------
        | Stream the file as a download
        |----------------------------------------------------------------------
        */

        $filename = 'balance-sheet-' . date('Y-m-d_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Individual Student Ledger
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);

        /*
        |----------------------------------------------------------------------
        | Active enrollment for class / session info
        |----------------------------------------------------------------------
        */

        $enrollment = PaEnrollment::with(['class', 'session'])
            ->where('student_id', $studentId)
            ->where('is_active', 1)
            ->latest()
            ->first();

        /*
        |----------------------------------------------------------------------
        | Date filter (optional)
        |----------------------------------------------------------------------
        */

        $fromDate = $request->from_date;
        $toDate   = $request->to_date;

        /*
        |----------------------------------------------------------------------
        | Previous Balance (ONLY when a from_date filter is applied)
        |
        | When no date filter is set, ALL vouchers are fetched and shown,
        | so there is no "previous" balance — the running balance is
        | calculated from scratch across every voucher + payment.
        |
        | When a from_date IS set, we compute the outstanding balance of
        | vouchers BEFORE that date as an opening balance, then only show
        | vouchers within the date range.  This avoids double-counting.
        |----------------------------------------------------------------------
        */

        $previousBalance = 0;
        $cutoff = null;

        if ($fromDate) {
            $cutoff = Carbon::parse($fromDate)->toDateString();

            $previousBalance = FeeVoucher::where('student_id', $studentId)
                ->outstanding()
                ->where('due_date', '<', $cutoff)
                ->sum('balance_amount');
        }

        /*
        |----------------------------------------------------------------------
        | All vouchers for this student (within the filter range, if any)
        |----------------------------------------------------------------------
        */

        $voucherQuery = FeeVoucher::with(['items.feeType', 'payments'])
            ->where('student_id', $studentId)
            ->orderBy('due_date');

        if ($fromDate) {
            $voucherQuery->where('due_date', '>=', $fromDate);
        }

        if ($toDate) {
            $voucherQuery->where('due_date', '<=', $toDate);
        }

        $vouchers = $voucherQuery->get();

        /*
        |----------------------------------------------------------------------
        | Summary Totals
        |----------------------------------------------------------------------
        */

        $totalCharged  = $vouchers->sum('payable_amount');
        $totalPaid     = $vouchers->sum('paid_amount');
        $totalDiscount = $vouchers->sum('discount');
        $totalBalance  = $vouchers->sum('balance_amount');

        // Grand outstanding = previous balance + current period balance
        $grandOutstanding = $previousBalance + $totalBalance;

        return view('student_ledger.show', compact(
            'student',
            'enrollment',
            'vouchers',
            'totalCharged',
            'totalPaid',
            'totalBalance',
            'totalDiscount',
            'previousBalance',
            'grandOutstanding',
            'fromDate',
            'toDate',
            'cutoff'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX: Get previous balance for a student (used by voucher create form)
    |--------------------------------------------------------------------------
    */

    public function getPreviousBalance(Request $request)
    {
        $request->validate(['student_id' => 'required|integer']);

        $studentId = $request->student_id;

        $cutoff = Carbon::now()->startOfMonth()->toDateString();

        $balance = FeeVoucher::where('student_id', $studentId)
            ->outstanding()
            ->where('due_date', '<', $cutoff)
            ->sum('balance_amount');

        // Also return breakdown of overdue vouchers for detail display
        $overdueVouchers = FeeVoucher::where('student_id', $studentId)
            ->outstanding()
            ->where('due_date', '<', $cutoff)
            ->orderBy('due_date')
            ->get(['id', 'voucher_no', 'due_date', 'payable_amount', 'paid_amount', 'balance_amount', 'status'])
            ->map(fn($v) => [
                'voucher_no'      => $v->voucher_no,
                'due_date'        => Carbon::parse($v->due_date)->format('M Y'),
                'payable_amount'  => $v->payable_amount,
                'paid_amount'     => $v->paid_amount,
                'balance_amount'  => $v->balance_amount,
                'status'          => $v->status,
            ]);

        return response()->json([
            'previous_balance'  => $balance,
            'overdue_vouchers'  => $overdueVouchers,
            'cutoff_date'       => $cutoff,
            'count'             => $overdueVouchers->count(),
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PaClass;
use App\Models\PaSession;
use App\Models\PaEnrollment;
use App\Models\FeeVoucher;

class FeeMatrixController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FEE MATRIX — a single grid: every student down the side, every month
    | across the top, and a paid/unpaid/overdue cell for each — plus one
    | column for admission/annual charges. Mirrors the Excel tracker.
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $sessions = PaSession::orderBy('id', 'desc')->get();
        $classes  = PaClass::orderBy('class_order')->get();

        $sessionId = $request->session_id ?: optional($sessions->firstWhere('is_active', 1))->id;
        $classId   = $request->class_id;

        // Default window: last 3 months through next 3 months. The old
        // "-1 / +2" window quietly dropped recently-paid vouchers from a
        // couple of months back (e.g. a May payment was invisible by July),
        // which looked like a bug even though the data was fine.
        $fromMonth = $request->from_month ?: now()->subMonths(3)->format('Y-m');
        $toMonth   = $request->to_month   ?: now()->addMonths(3)->format('Y-m');

        $months = $this->buildMonthList($fromMonth, $toMonth);

        $enrollments = PaEnrollment::with(['student', 'class'])
            ->where('is_active', 1)
            ->where('status', 'active')
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->get()
            ->filter(fn($e) => $e->student !== null)
            ->sortBy([
                fn($e) => $e->class->class_order ?? 999,
                fn($e) => $e->student->student_name,
            ])
            ->values();

        $studentIds = $enrollments->pluck('student_id');

        // ── Admission / Annual charges ──
        //
        // Previously this only looked at vouchers with voucher_type =
        // 'admission' — but "Annual Charges" is a FeeType with
        // category = 'yearly', and can be billed on its OWN voucher
        // (voucher_type = 'manual', created any time, not just at
        // enrollment). That voucher was invisible to this column entirely.
        //
        // Fixed by looking at the actual fee-type ITEMS instead of the
        // voucher's type: any voucher carrying an "admission" category item
        // (Admission Fee, Registration, Test/File Charges, etc.) OR the
        // specific "Annual Charges" fee type (matched by its stable `code`,
        // not by name, and NOT the whole 'yearly' category — Course Charges
        // and Copies Charges are also tagged 'yearly' but are different
        // charges, not what this column is meant to track).
        //
        // A student can have more than one such voucher (e.g. Admission Fee
        // billed at enrollment, Annual Charges billed separately later) —
        // figures are combined into one payable/paid/balance per student.
        $acItems = \App\Models\FeeVoucherItem::with(['voucher.payments', 'feeType'])
            ->whereHas('feeType', function ($q) {
                $q->where('category', 'admission')
                  ->orWhere('code', 'ANNUAL_CHARGES');
            })
            ->whereHas('voucher', fn($q) => $q->whereIn('student_id', $studentIds))
            ->get()
            ->filter(fn($item) => $item->voucher !== null);

        // Group by student, then de-duplicate to distinct vouchers (a
        // voucher can carry more than one matching item, e.g. Admission Fee
        // + Registration Form Charges together) before summing amounts —
        // otherwise a voucher with 2 matching items would be double-counted.
        $admissionByStudent = $acItems
            ->groupBy(fn($item) => $item->voucher->student_id)
            ->map(function ($items) {
                return $items->pluck('voucher')->unique('id')->values();
            });

        // ── Monthly fee coverage within the visible window ──
        //
        // IMPORTANT: bucketed by FEE VOUCHER ITEM month, not by the parent
        // voucher's period_from. A voucher can carry several monthly-fee
        // line items covering different months (e.g. one voucher generated
        // in April with items for April + June + July, when a parent pays
        // several months in advance). Bucketing by the voucher's single
        // period_from would only ever show it under April and leave June/
        // July looking like "no voucher" even though they were invoiced.
        // Bucketing by each item's own `month` fixes that: the same voucher
        // now appears under all three columns, each showing that month's
        // specific line-item amount.
        $rangeStart = $fromMonth . '-01';
        $rangeEnd   = Carbon::parse($toMonth . '-01')->endOfMonth()->format('Y-m-d');

        $monthlyItems = \App\Models\FeeVoucherItem::with(['voucher.payments', 'voucher.items.feeType', 'feeType'])
            ->whereHas('feeType', fn($q) => $q->where('category', 'monthly'))
            ->whereHas('voucher', fn($q) => $q->whereIn('student_id', $studentIds))
            ->whereBetween('month', [$rangeStart, $rangeEnd])
            ->get()
            ->filter(fn($item) => $item->voucher !== null)
            ->groupBy(fn($item) => $item->voucher->student_id)
            ->map(function ($items) {
                // Within one student, key by the item's own month — if two
                // items land on the same month (shouldn't normally happen),
                // the later one wins.
                return $items->keyBy(fn($item) => Carbon::parse($item->month)->format('Y-m'));
            });

        // Vouchers that fall in the window purely by period_from/due_date
        // but have NO monthly-category items at all (legacy vouchers with
        // no item breakdown) — fall back to the old voucher-level bucketing
        // so nothing that used to show up silently disappears.
        $fallbackVouchers = FeeVoucher::with('payments')
            ->where('voucher_type', 'monthly')
            ->whereIn('student_id', $studentIds)
            ->whereDoesntHave('items.feeType', fn($q) => $q->where('category', 'monthly'))
            ->where(function ($q) use ($rangeStart, $rangeEnd) {
                $q->whereBetween('period_from', [$rangeStart, $rangeEnd])
                  ->orWhere(function ($q2) use ($rangeStart, $rangeEnd) {
                      $q2->whereNull('period_from')
                         ->whereBetween('due_date', [$rangeStart, $rangeEnd]);
                  });
            })
            ->get()
            ->groupBy('student_id')
            ->map(function ($vouchers) {
                return $vouchers->keyBy(function ($v) {
                    $anchor = $v->period_from ?? $v->due_date;
                    return $anchor ? Carbon::parse($anchor)->format('Y-m') : 'unknown';
                });
            });

        // ── Build the grid rows ──
        $rows = $enrollments->map(function ($enrollment) use ($months, $admissionByStudent, $monthlyItems, $fallbackVouchers) {

            $student = $enrollment->student;

            $acVouchers = $admissionByStudent->get($student->id, collect());

            $cells = collect($months)->mapWithKeys(function ($month) use ($student, $monthlyItems, $fallbackVouchers) {
                $item = optional($monthlyItems->get($student->id))->get($month['key']);

                if ($item) {
                    return [$month['key'] => $this->cellData($item->voucher, $item)];
                }

                // Legacy voucher with no itemized monthly breakdown
                $voucher = optional($fallbackVouchers->get($student->id))->get($month['key']);
                return [$month['key'] => $this->cellData($voucher)];
            });

            $admissionData = null;
            if ($acVouchers->isNotEmpty()) {
                $admPayable = (float) $acVouchers->sum('payable_amount');
                $admPaid    = (float) $acVouchers->sum('paid_amount');
                $admBalance = (float) $acVouchers->sum('balance_amount');

                $lastPayment = $acVouchers
                    ->flatMap(fn($v) => $v->payments)
                    ->sortByDesc('payment_date')
                    ->first();

                $admissionData = [
                    // Links to the first voucher when there's only one;
                    // when there are several, the count is shown instead so
                    // the person knows to check the student's profile for
                    // the full breakdown rather than jumping to just one.
                    'voucher_id'    => $acVouchers->count() === 1 ? $acVouchers->first()->id : null,
                    'voucher_count' => $acVouchers->count(),
                    'amount'        => $admPayable,
                    'paid_amount'   => $admPaid,
                    'balance'       => $admBalance,
                    'state'         => $admBalance <= 0 ? 'paid' : ($admBalance < $admPayable ? 'partial' : 'unpaid'),
                    'paid_date'     => optional($lastPayment)->payment_date,
                ];
            }

            return [
                'enrollment' => $enrollment,
                'student'    => $student,
                'class'      => $enrollment->class,
                'comments'   => $enrollment->notes,
                'admission'  => $admissionData,
                'cells' => $cells,
            ];
        });

        // Group rows by class for the shaded class-blocks seen in the Excel sheet.
        $groupedRows = $rows->groupBy(fn($row) => $row['class']->id ?? 0);

        $stats = [
            'total_students'   => $rows->count(),
            'no_admission'     => $rows->whereNull('admission')->count(),
            'unpaid_admission' => $rows->filter(fn($r) => $r['admission'] && $r['admission']['state'] !== 'paid')->count(),
        ];

        return view('fee_matrix.index', compact(
            'sessions', 'classes', 'sessionId', 'classId',
            'fromMonth', 'toMonth', 'months', 'groupedRows', 'stats'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT — same grid, flattened to CSV.
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        // Reuse index()'s query logic by calling it and pulling the view data
        // back out would require refactoring into a shared builder; instead,
        // keep this simple and rebuild the same grid directly.
        $response = $this->index($request);
        $data     = $response->getData();

        $filename = 'fee-matrix_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            $columns = ['Reg No', 'Student Name', 'Contact Number', 'Class', 'Comments', 'Admission/Annual Fee', 'A/C Status'];
            foreach ($data['months'] as $month) {
                $columns[] = $month['label'];
            }
            fputcsv($file, $columns);

            foreach ($data['groupedRows'] as $classRows) {
                foreach ($classRows as $row) {
                    $line = [
                        $row['student']->admission_no,
                        $row['student']->student_name,
                        $row['student']->mobile_no ?: $row['student']->guardian_mobile,
                        $row['class']->class_name ?? '',
                        $row['comments'] ?? '',
                        $row['admission']['amount'] ?? '',
                        $row['admission'] ? ucfirst($row['admission']['state']) : 'N/A',
                    ];
                    foreach ($data['months'] as $month) {
                        $cell = $row['cells'][$month['key']];
                        $line[] = $cell['label'];
                    }
                    fputcsv($file, $line);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function buildMonthList(string $from, string $to): array
    {
        $months = [];
        $cursor = Carbon::parse($from . '-01');
        $end    = Carbon::parse($to . '-01');

        while ($cursor <= $end) {
            $months[] = [
                'key'   => $cursor->format('Y-m'),
                'label' => $cursor->format('M-y'),
            ];
            $cursor->addMonth();
        }

        return $months;
    }

    private function cellData($voucher, $item = null): array
    {
        if (!$voucher) {
            return ['state' => 'none', 'label' => '—', 'amount' => null, 'date' => null, 'voucher_id' => null, 'voucher_no' => null, 'multi_month' => false];
        }

        $lastPayment = $voucher->payments->sortByDesc('payment_date')->first();
        $payable     = (float) $voucher->payable_amount;
        $balance     = (float) $voucher->balance_amount;

        // How many distinct months does this voucher's monthly-category
        // items actually cover? >1 means it's a multi-month voucher (e.g.
        // April + June + July paid together) — flagged so the UI can make
        // clear the amount shown is that month's slice of a bigger voucher,
        // and that the paid/unpaid colour reflects the WHOLE voucher, not
        // just this one month (payments aren't tracked per line item).
        $isMultiMonth = false;
        if ($item && $voucher->relationLoaded('items')) {
            $isMultiMonth = $voucher->items
                ->filter(fn($i) => optional($i->feeType)->category === 'monthly')
                ->pluck('month')
                ->map(fn($m) => Carbon::parse($m)->format('Y-m'))
                ->unique()
                ->count() > 1;
        }

        // This month's specific amount: the line item's own amount when we
        // have one, otherwise the voucher's full payable amount (legacy /
        // single-month vouchers with no item breakdown).
        $monthAmount = $item ? (float) $item->amount : $payable;

        // Colour is driven purely by the amounts on the VOUCHER as a whole —
        // not the stored `status` string and not the due date — so it can
        // never drift out of sync with what was actually paid:
        //   balance == 0            → fully paid            (green)
        //   0 < balance < payable   → partially paid         (yellow)
        //   balance == payable      → nothing paid yet at all (red)

        if ($balance <= 0) {
            return [
                'state'       => 'paid',
                'label'       => number_format($monthAmount, 0),
                'amount'      => $monthAmount,
                'date'        => $lastPayment ? Carbon::parse($lastPayment->payment_date)->format('d-M-y') : null,
                'voucher_id'  => $voucher->id,
                'voucher_no'  => $voucher->voucher_no,
                'multi_month' => $isMultiMonth,
            ];
        }

        if ($balance < $payable) {
            return [
                'state'       => 'partial',
                'label'       => number_format($monthAmount, 0),
                'amount'      => $monthAmount,
                'date'        => $lastPayment ? Carbon::parse($lastPayment->payment_date)->format('d-M-y') : null,
                'voucher_id'  => $voucher->id,
                'voucher_no'  => $voucher->voucher_no,
                'multi_month' => $isMultiMonth,
            ];
        }

        // balance == payable — nothing has been paid at all.
        return [
            'state'       => 'unpaid',
            'label'       => 'Rs ' . number_format($monthAmount, 0),
            'amount'      => $monthAmount,
            'date'        => $voucher->due_date ? Carbon::parse($voucher->due_date)->format('d-M-y') : null,
            'voucher_id'  => $voucher->id,
            'voucher_no'  => $voucher->voucher_no,
            'multi_month' => $isMultiMonth,
        ];
    }
}
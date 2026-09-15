<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;
use App\Models\PaClass;
use App\Models\FeeType;
use App\Models\FeeVoucher;
use App\Models\FeeVoucherItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FeeVoucherController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $search        = $request->search;
        $query         = $search;
        $statusFilter  = $request->status;
        $studentFilter = $request->student_id;
        $classFilter   = $request->class_id;
        $monthFilter   = $request->month;
        $sortFilter    = $request->sort ?? 'latest';

        $vouchers = FeeVoucher::with([
            'student',
            'student.enrollments.class',
            'payments'
        ])

        ->when($search, function ($q) use ($search) {

            $q->where(function ($sub) use ($search) {
                $sub->where('voucher_no', 'like', "%{$search}%")

                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('student_name', 'like', "%{$search}%")
                           ->orWhere('admission_no', 'like', "%{$search}%");
                    });
            });

        })

        ->when($statusFilter, function ($q) use ($statusFilter) {
            $q->where('status', strtolower($statusFilter));
        })

        ->when($studentFilter, function ($q) use ($studentFilter) {
            $q->where('student_id', $studentFilter);
        })

        ->when($classFilter, function ($q) use ($classFilter) {
            $q->whereHas('student.enrollments', function ($enrollment) use ($classFilter) {
                $enrollment->where('class_id', $classFilter);
            });
        })

        ->when($monthFilter, function ($q) use ($monthFilter) {
            $q->whereMonth('period_from', $monthFilter);
        });

        // Sorting
        if ($sortFilter == 'oldest') {
            $vouchers->orderBy('created_at', 'asc');
        } else {
            $vouchers->orderBy('created_at', 'desc');
        }

        $vouchers = $vouchers->paginate(25)->withQueryString();

        $students = Student::where('is_active', 1)
            ->orderBy('student_name')
            ->get();

        $classes = PaClass::orderBy('class_order')->get();

        return view('fee_vouchers.index', compact(
            'vouchers',
            'query',
            'search',
            'statusFilter',
            'studentFilter',
            'classFilter',
            'monthFilter',
            'sortFilter',
            'students',
            'classes'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $classes  = PaClass::orderBy('class_order')->get();

        $students = Student::with('enrollments.class')
            ->where('is_active', 1)
            ->orderBy('student_name')
            ->get();

        $feeTypes = FeeType::where('is_active', 1)->get();

        $preselectedStudentId   = $request->student_id;
        $preselectedPrevBalance = 0;
        $preselectedOverdue     = [];

        if ($preselectedStudentId) {

            // Same reasoning as StudentLedgerController@getPreviousBalance:
            // no due-date cutoff — any unpaid/partial voucher counts here,
            // not just ones due before this calendar month.
            $preselectedPrevBalance = FeeVoucher::where('student_id', $preselectedStudentId)
                ->outstanding()
                ->sum('balance_amount');

            // Map to plain array HERE in the controller
            $preselectedOverdue = FeeVoucher::where('student_id', $preselectedStudentId)
                ->outstanding()
                ->orderBy('due_date')
                ->get()
                ->map(function ($v) {
                    return [
                        'id'             => $v->id,
                        'voucher_no'     => $v->voucher_no,
                        'due_date'       => Carbon::parse($v->due_date)->format('M Y'),
                        'payable_amount' => (float) $v->payable_amount,
                        'paid_amount'    => (float) $v->paid_amount,
                        'balance_amount' => (float) $v->balance_amount,
                        'status'         => $v->status,
                    ];
                })
                ->values()
                ->toArray();
        }

        // Build a class→feeType→unitRate lookup for JS auto-fill
        // Shape: { classId: { feeTypeId: amount, ... }, ... }
        $classFeeMap = \App\Models\ClassFeeStructure::where('is_active', 1)
            ->get(['class_id', 'fee_type_id', 'amount'])
            ->groupBy('class_id')
            ->map(fn($rows) => $rows->pluck('amount', 'fee_type_id'))
            ->toArray();

        return view('fee_vouchers.create', compact(
            'classes',
            'students',
            'feeTypes',
            'preselectedStudentId',
            'preselectedPrevBalance',
            'preselectedOverdue',
            'classFeeMap'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'student_id'     => 'required|exists:students,id',
            'period_from'    => 'required|date',
            'period_to'      => 'required|date|after_or_equal:period_from',
            'payable_amount' => 'required|numeric|min:0',
            'due_date'       => 'required|date',
        ]);

        $carriedForwardCount = 0;

        $voucher = DB::transaction(function () use ($request, &$carriedForwardCount) {

            $voucherNo = 'FV-' . date('YmdHis');

            $voucher = FeeVoucher::create([
                'voucher_no'      => $voucherNo,
                'student_id'      => $request->student_id,
                'voucher_type'    => $request->voucher_type ?? 'monthly',
                'period_from'     => $request->period_from,
                'period_to'       => $request->period_to,
                'total_amount'    => $request->total_amount ?? $request->payable_amount,
                'discount'        => $request->discount ?? 0,
                'payable_amount'  => $request->payable_amount,
                'paid_amount'     => 0,
                'balance_amount'  => $request->payable_amount,
                'amount_in_words' => $request->amount_in_words,
                'due_date'        => $request->due_date,
                'status'          => 'unpaid',
                'notes'           => $request->notes,
            ]);

            if ($request->fee_type_id) {

                foreach ($request->fee_type_id as $key => $feeTypeId) {

                    // <input type="month"> sends "YYYY-MM";
                    // MySQL DATE needs "YYYY-MM-DD"
                    $monthValue = $request->month[$key] ?? null;

                    if ($monthValue && strlen($monthValue) === 7) {
                        $monthValue = $monthValue . '-01';
                    }

                    FeeVoucherItem::create([
                        'voucher_id'   => $voucher->id,
                        'fee_type_id'  => $feeTypeId,
                        'description'  => $request->description[$key] ?? null,
                        'month'        => $monthValue,
                        'months_count' => $request->months_count[$key] ?? 1,
                        'amount'       => $request->amount[$key],
                    ]);
                }
            }


            /*
            |------------------------------------------------------------------
            | Previous Balance
            |------------------------------------------------------------------
            */

            $selectedVoucherIds = collect($request->input('selected_previous_vouchers', []))
                ->filter(fn($id) => is_numeric($id))
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            if ($selectedVoucherIds->isNotEmpty()) {

                $sourceVouchers = FeeVoucher::where('student_id', $request->student_id)
                    ->where('id', '!=', $voucher->id)
                    ->whereIn('id', $selectedVoucherIds)
                    ->outstanding()
                    ->orderByDesc('due_date')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->get();

                $previousBalance = (float) $sourceVouchers->sum('balance_amount');

                if ($previousBalance > 0) {

                    $feeType = FeeType::firstOrCreate(
                        ['name' => 'Previous Balance'],
                        [
                            'category' => 'other',
                            'is_active' => 1,
                            'description' => 'Previous outstanding balance'
                        ]
                    );

                    $latestSource = $sourceVouchers->first();

                    $refLabel = $latestSource
                        ? ' (Ref: ' . $latestSource->voucher_no
                            . ($sourceVouchers->count() > 1
                                ? ' +' . ($sourceVouchers->count() - 1) . ' more'
                                : '')
                            . ')'
                        : '';

                    FeeVoucherItem::create([
                        'voucher_id'   => $voucher->id,
                        'fee_type_id'  => $feeType->id,
                        'description'  => 'Previous outstanding balance (b/f)' . $refLabel,
                        'month'        => $request->period_from,
                        'months_count' => 1,
                        'amount'       => $previousBalance,
                    ]);

                    $newTotal = $voucher->payable_amount + $previousBalance;

                    $voucher->update([
                        'total_amount'               => $voucher->total_amount + $previousBalance,
                        'payable_amount'             => $newTotal,
                        'balance_amount'             => $newTotal,
                        'previous_balance_voucher_id' => $latestSource?->id,
                    ]);

                    foreach ($sourceVouchers as $sourceVoucher) {
                        $sourceVoucher->markAsCarriedForwardTo($voucher);
                    }

                    $carriedForwardCount = $sourceVouchers->count();
                }
            }

            return $voucher;
        });

        $message = 'Fee Voucher ' . $voucher->voucher_no . ' created successfully.';

        if ($carriedForwardCount > 0) {
            $message .= " {$carriedForwardCount} old voucher(s) marked Carried Forward (C.F).";
        }

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', $message);
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $voucher = FeeVoucher::with([
            'items',
            'student',
            'payments'
        ])->findOrFail($id);

        if (in_array($voucher->status, ['paid', 'carried_forward'], true)) {

            $reason = $voucher->status === 'paid'
                ? 'This voucher is fully paid and can no longer be edited. Use "Fix Method" on that voucher to correct the payment method or reference number instead.'
                : 'This voucher has been carried forward into voucher '
                    . optional($voucher->carriedForwardTo)->voucher_no
                    . ' and can no longer be edited.';

            return redirect()
                ->route('fee-vouchers.index')
                ->with('error', $reason);
        }

        // Partial payment lock
        $hasPayments = $voucher->payments->count() > 0;

        $classes = PaClass::orderBy('class_order')->get();

        $students = Student::with('enrollments.class')
            ->orderBy('student_name')
            ->get();

        $feeTypes = FeeType::where('is_active', 1)->get();

        // Build class→feeType→unitRate lookup for JS auto-fill
        $classFeeMap = \App\Models\ClassFeeStructure::where('is_active', 1)
            ->get(['class_id', 'fee_type_id', 'amount'])
            ->groupBy('class_id')
            ->map(fn($rows) => $rows->pluck('amount', 'fee_type_id'))
            ->toArray();

        return view(
            'fee_vouchers.edit',
            compact(
                'voucher',
                'classes',
                'students',
                'feeTypes',
                'classFeeMap',
                'hasPayments'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $voucher = FeeVoucher::with('payments')->findOrFail($id);

        if (in_array($voucher->status, ['paid', 'carried_forward'], true)) {

            $reason = $voucher->status === 'paid'
                ? 'This voucher is fully paid and can no longer be edited. Use "Fix Method" on that voucher to correct the payment method or reference number instead.'
                : 'This voucher has been carried forward into voucher '
                    . optional($voucher->carriedForwardTo)->voucher_no
                    . ' and can no longer be edited.';

            return redirect()
                ->route('fee-vouchers.index')
                ->with('error', $reason);
        }

        $hasPayments = $voucher->payments->count() > 0;

        if ($hasPayments) {

            $request->validate([
                'period_from' => 'required|date',
                'period_to'   => 'required|date|after_or_equal:period_from',
                'due_date'    => 'required|date',
            ]);

        } else {

            $request->validate([
                'student_id'     => 'required|exists:students,id',
                'period_from'    => 'required|date',
                'period_to'      => 'required|date|after_or_equal:period_from',
                'payable_amount' => 'required|numeric|min:0',
                'due_date'       => 'required|date',
            ]);
        }

        DB::transaction(function () use ($request, $voucher, $hasPayments) {

            $updateData = [
                'period_from' => $request->period_from,
                'period_to'   => $request->period_to,
                'due_date'    => $request->due_date,
                'notes'       => $request->notes,
            ];

            if (!$hasPayments) {

                $updateData['student_id']      = $request->student_id;
                $updateData['total_amount']    = $request->total_amount;
                $updateData['discount']        = $request->discount ?? 0;
                $updateData['payable_amount']  = $request->payable_amount;
                $updateData['amount_in_words'] = $request->amount_in_words;
            }

            $voucher->update($updateData);

            // Rebuild items only when nothing has been paid yet
            if (!$hasPayments) {

                FeeVoucherItem::where('voucher_id', $voucher->id)->delete();

                if ($request->fee_type_id) {

                    foreach ($request->fee_type_id as $key => $feeTypeId) {

                        // <input type="month"> sends "YYYY-MM"
                        $monthValue = $request->month[$key] ?? null;

                        if ($monthValue && strlen($monthValue) === 7) {
                            $monthValue = $monthValue . '-01';
                        }

                        FeeVoucherItem::create([
                            'voucher_id'   => $voucher->id,
                            'fee_type_id'  => $feeTypeId,
                            'description'  => $request->description[$key] ?? null,
                            'month'        => $monthValue,
                            'months_count' => $request->months_count[$key] ?? 1,
                            'amount'       => $request->amount[$key],
                        ]);
                    }
                }
            }

            // Recalculate paid/balance/status from actual payments
            $voucher->recalculateBalance();
        });

        return redirect()
            ->route('fee-vouchers.index')
            ->with('success', 'Fee voucher updated successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print($id)
    {
        $voucher = FeeVoucher::with([
            'student',
            'items.feeType',
            'payments'
        ])->findOrFail($id);

        $cutoff = Carbon::now()->startOfMonth()->toDateString();

        $previousBalance = FeeVoucher::where('student_id', $voucher->student_id)
            ->outstanding()
            ->where('due_date', '<', $cutoff)
            ->where('balance_amount', '>', 0)
            ->where('id', '!=', $voucher->id)
            ->sum('balance_amount');

        return view(
            'fee_vouchers.print',
            compact('voucher', 'previousBalance')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE PDF
    |--------------------------------------------------------------------------
    |
    | Generates a server-side PDF and saves it automatically under:
    |
    | storage/app/public/fee-vouchers/
    |     Month Year/
    |         Class Name/
    |             Voucher No - Student Name.pdf
    |
    */

    public function savePdf($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Load complete voucher
        |--------------------------------------------------------------------------
        */

        $voucher = FeeVoucher::with([
            'student',
            'items.feeType',
            'payments'
        ])->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Calculate previous balance
        |--------------------------------------------------------------------------
        |
        | This uses the same logic as the existing Print method so that
        | the saved PDF displays the same previous balance.
        |
        */

        $cutoff = Carbon::now()->startOfMonth()->toDateString();

        $previousBalance = FeeVoucher::where('student_id', $voucher->student_id)
            ->outstanding()
            ->where('due_date', '<', $cutoff)
            ->where('balance_amount', '>', 0)
            ->where('id', '!=', $voucher->id)
            ->sum('balance_amount');


        /*
        |--------------------------------------------------------------------------
        | Determine Month Folder
        |--------------------------------------------------------------------------
        */

        $monthFolder = Carbon::parse($voucher->period_from)
            ->format('F Y');


        /*
        |--------------------------------------------------------------------------
        | Determine Class Folder
        |--------------------------------------------------------------------------
        */

        $className = $voucher->student?->activeEnrollment?->class?->class_name
            ?? 'Unknown Class';


        /*
        |--------------------------------------------------------------------------
        | Clean Folder Name
        |--------------------------------------------------------------------------
        |
        | Prevent characters such as / \ : * ? " < > | from creating
        | invalid or unwanted folder names.
        |
        */

        $classFolder = preg_replace(
            '/[^\pL\pN\s_-]+/u',
            '',
            $className
        );

        $classFolder = trim(
            preg_replace('/\s+/', ' ', $classFolder)
        );

        if (!$classFolder) {
            $classFolder = 'Unknown Class';
        }


        /*
        |--------------------------------------------------------------------------
        | Student Name
        |--------------------------------------------------------------------------
        */

        $studentName = $voucher->student?->student_name
            ?? 'Student';


        /*
        |--------------------------------------------------------------------------
        | Clean Student Name For Filename
        |--------------------------------------------------------------------------
        */

        $safeStudentName = preg_replace(
            '/[^\pL\pN\s_-]+/u',
            '',
            $studentName
        );

        $safeStudentName = trim(
            preg_replace('/\s+/', ' ', $safeStudentName)
        );

        if (!$safeStudentName) {
            $safeStudentName = 'Student';
        }


        /*
        |--------------------------------------------------------------------------
        | PDF Filename
        |--------------------------------------------------------------------------
        */

        $filename = $voucher->voucher_no
            . ' - '
            . $safeStudentName
            . '.pdf';


        /*
        |--------------------------------------------------------------------------
        | Complete Storage Path
        |--------------------------------------------------------------------------
        */

        $directory = 'fee-vouchers/'
            . $monthFolder
            . '/'
            . $classFolder;

        $filePath = $directory . '/' . $filename;


        /*
        |--------------------------------------------------------------------------
        | Prepare Logo For DomPDF
        |--------------------------------------------------------------------------
        |
        | DomPDF works more reliably with a local image converted to
        | Base64 rather than relying on asset() URLs.
        |
        */

        $logoData = null;

        $logoPath = public_path('images/logo.png');

        if (file_exists($logoPath)) {

            $logoData = 'data:image/png;base64,'
                . base64_encode(
                    file_get_contents($logoPath)
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'fee_vouchers.pdf',
            compact(
                'voucher',
                'previousBalance',
                'logoData'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | A4 Page
        |--------------------------------------------------------------------------
        */

        $pdf->setPaper('a4', 'portrait');


        /*
        |--------------------------------------------------------------------------
        | Save PDF
        |--------------------------------------------------------------------------
        |
        | Storage disk "public" points to:
        |
        | storage/app/public
        |
        */

        Storage::disk('public')->put(
            $filePath,
            $pdf->output()
        );


        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->back()
            ->with(
                'success',
                'PDF saved successfully: '
                . $monthFolder
                . ' / '
                . $classFolder
                . ' / '
                . $filename
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    |
    | Used to remove duplicate / mistakenly-created vouchers.
    | A voucher can only be deleted if NO payment has ever been recorded.
    |
    */

    public function destroy($id)
    {
        $voucher = FeeVoucher::withCount('payments')->findOrFail($id);

        if ($voucher->payments_count > 0) {

            return redirect()
                ->route('fee-vouchers.index')
                ->with(
                    'error',
                    'Voucher ' . $voucher->voucher_no
                    . ' has payment(s) recorded against it and cannot be deleted. '
                    . 'Reverse the payment(s) from Payment History first, then delete the voucher.'
                );
        }

        if ($voucher->status === 'carried_forward') {

            return redirect()
                ->route('fee-vouchers.index')
                ->with(
                    'error',
                    'Voucher ' . $voucher->voucher_no
                    . ' has been carried forward into voucher '
                    . optional($voucher->carriedForwardTo)->voucher_no
                    . ' and cannot be deleted, to preserve the balance history.'
                );
        }

        DB::transaction(function () use ($voucher) {

            FeeVoucherItem::where(
                'voucher_id',
                $voucher->id
            )->delete();

            $voucher->delete();
        });

        return redirect()
            ->route('fee-vouchers.index')
            ->with(
                'success',
                'Fee Voucher '
                . $voucher->voucher_no
                . ' deleted successfully.'
            );
    }
}

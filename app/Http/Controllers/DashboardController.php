<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\PaClass;
use App\Models\FeeVoucher;
use App\Models\FeePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */
        $totalStudents  = Student::count();
        $activeStudents = Student::where('is_active', 1)->count();

        /*
        |--------------------------------------------------------------------------
        | Classes
        |--------------------------------------------------------------------------
        */
        $totalClasses = PaClass::where('is_active', 1)->count();

        /*
        |--------------------------------------------------------------------------
        | Voucher Statistics
        |--------------------------------------------------------------------------
        */
        $totalVouchers   = FeeVoucher::count();
        $unpaidVouchers  = FeeVoucher::where('status', 'unpaid')->count();
        $partialVouchers = FeeVoucher::where('status', 'partial')->count();
        $paidVouchers    = FeeVoucher::where('status', 'paid')->count();

        /*
        |--------------------------------------------------------------------------
        | Financial Summary
        |--------------------------------------------------------------------------
        */
        $totalFeeGenerated = FeeVoucher::sum('payable_amount');
        $totalCollected    = FeePayment::sum('amount_paid');
        $totalOutstanding  = FeeVoucher::sum('balance_amount');

        /*
        |--------------------------------------------------------------------------
        | Today's Collection
        |--------------------------------------------------------------------------
        */
        $todayCollection = FeePayment::whereDate('payment_date', $now->toDateString())
                                      ->sum('amount_paid');

        /*
        |--------------------------------------------------------------------------
        | Current Month Collection & Outstanding
        |--------------------------------------------------------------------------
        */
        $monthCollection = FeePayment::whereYear('payment_date', $now->year)
                                      ->whereMonth('payment_date', $now->month)
                                      ->sum('amount_paid');

        $currentMonthBilled = FeeVoucher::whereYear('created_at', $now->year)
                                         ->whereMonth('created_at', $now->month)
                                         ->sum('payable_amount');

        $currentMonthOutstanding = FeeVoucher::where('status', 'unpaid')
                                              ->whereYear('created_at', $now->year)
                                              ->whereMonth('created_at', $now->month)
                                              ->sum('balance_amount');

        $collectionRate = $currentMonthBilled > 0
            ? round(($monthCollection / $currentMonthBilled) * 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Annual Summary
        |--------------------------------------------------------------------------
        */
        $annualBilled      = FeeVoucher::whereYear('created_at', $now->year)->sum('payable_amount');
        $annualCollected   = FeePayment::whereYear('payment_date', $now->year)->sum('amount_paid');
        $annualOutstanding = FeeVoucher::where('status', 'unpaid')
                                        ->whereYear('created_at', $now->year)
                                        ->sum('balance_amount');
        $annualCollectionRate = $annualBilled > 0
            ? round(($annualCollected / $annualBilled) * 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Overdue & Action Counts
        |--------------------------------------------------------------------------
        */
        $overdueVouchers = FeeVoucher::where('status', 'unpaid')
            ->where('due_date', '<', $now->toDateString())
            ->count();

        $dueThisWeek = FeeVoucher::where('status', 'unpaid')
            ->whereBetween('due_date', [
                $now->toDateString(),
                $now->copy()->addDays(7)->toDateString(),
            ])->count();

        $paidThisWeek = FeeVoucher::where('status', 'paid')
            ->whereBetween('updated_at', [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ])->count();

        /*
        |--------------------------------------------------------------------------
        | Monthly Summary — last 6 months
        |--------------------------------------------------------------------------
        */
        $monthlySummary = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);

            $billed = FeeVoucher::whereYear('created_at', $m->year)
                                 ->whereMonth('created_at', $m->month)
                                 ->sum('payable_amount');

            $collected = FeePayment::whereYear('payment_date', $m->year)
                                    ->whereMonth('payment_date', $m->month)
                                    ->sum('amount_paid');

            $outstanding = FeeVoucher::where('status', 'unpaid')
                                      ->whereYear('created_at', $m->year)
                                      ->whereMonth('created_at', $m->month)
                                      ->sum('balance_amount');

            $vcount = FeeVoucher::whereYear('created_at', $m->year)
                                  ->whereMonth('created_at', $m->month)
                                  ->count();

            $monthlySummary[] = [
                'month'         => $m->format('Y-m'),
                'label'         => $m->format('M Y'),
                'billed'        => (float) $billed,
                'collected'     => (float) $collected,
                'outstanding'   => (float) $outstanding,
                'voucher_count' => (int) $vcount,
                'rate'          => $billed > 0 ? round(($collected / $billed) * 100) : 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Top Defaulters — uses Eloquent to avoid column-name guessing
        |--------------------------------------------------------------------------
        */
        $topDefaulters = FeeVoucher::with('student')
            ->outstanding()
            ->select('student_id', DB::raw('SUM(balance_amount) as outstanding'), DB::raw('COUNT(id) as voucher_count'))
            ->groupBy('student_id')
            ->orderByDesc('outstanding')
            ->limit(5)
            ->get()
            ->map(function ($fv) {
                $student = $fv->student;
                $enrollment = $student?->activeEnrollment;

                return [
                    'name'        => $student?->student_name ?? 'Unknown',
                    'class'       => $enrollment?->class?->class_name ?? 'N/A',
                    'outstanding' => (float) $fv->outstanding,
                    'months'      => (int) $fv->voucher_count,
                ];
            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Recent Vouchers
        |--------------------------------------------------------------------------
        */
        $recentVouchers = FeeVoucher::with('student')
                                     ->latest()
                                     ->take(10)
                                     ->get();

        /*
        |--------------------------------------------------------------------------
        | Chart Data — 12-month rolling
        |--------------------------------------------------------------------------
        */
        $chartLabels    = [];
        $chartBilled    = [];
        $chartCollected = [];
        $chartRates     = [];

        for ($i = 11; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);

            $b = FeeVoucher::whereYear('created_at', $m->year)
                            ->whereMonth('created_at', $m->month)
                            ->sum('payable_amount');

            $c = FeePayment::whereYear('payment_date', $m->year)
                            ->whereMonth('payment_date', $m->month)
                            ->sum('amount_paid');

            $chartLabels[]    = $m->format('M y');
            $chartBilled[]    = (float) $b;
            $chartCollected[] = (float) $c;
            $chartRates[]     = $b > 0 ? round(($c / $b) * 100) : 0;
        }

        return view('dashboard.index', compact(
            // originals — untouched
            'totalStudents', 'activeStudents',
            'totalClasses',
            'totalVouchers', 'unpaidVouchers', 'partialVouchers', 'paidVouchers',
            'totalFeeGenerated', 'totalCollected', 'totalOutstanding',
            'todayCollection', 'monthCollection',
            'recentVouchers',
            // new
            'currentMonthBilled', 'currentMonthOutstanding', 'collectionRate',
            'annualBilled', 'annualCollected', 'annualOutstanding', 'annualCollectionRate',
            'overdueVouchers', 'dueThisWeek', 'paidThisWeek',
            'monthlySummary', 'topDefaulters',
            'chartLabels', 'chartBilled', 'chartCollected', 'chartRates'
        ));
    }
}
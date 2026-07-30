<?php
namespace App\Http\Controllers;
use App\Models\FeeVoucher;
use App\Models\FeePayment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyLedgerController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $monthStart = $month . '-01';
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        /*
        |--------------------------------------------------------------
        | INCOME SIDE — fee payments received in this calendar month
        |--------------------------------------------------------------
        */
        $payments = FeePayment::with(['student','voucher'])
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->orderBy('payment_date')
            ->get();

        $incomeByMethod = $payments->groupBy('payment_method')
            ->map(fn($g) => $g->sum('amount_paid'));

        $totalIncome = $payments->sum('amount_paid');

        /*
        |--------------------------------------------------------------
        | EXPENSE SIDE
        |--------------------------------------------------------------
        */
        $expenses = Expense::where('expense_month', $month)
            ->orderBy('expense_date')
            ->get();

        $expenseByCategory = $expenses->groupBy('category')
            ->map(fn($g) => $g->sum('amount'));

        $totalExpenses = $expenses->sum('amount');

        /*
        |--------------------------------------------------------------
        | NET
        |--------------------------------------------------------------
        */
        $netBalance = $totalIncome - $totalExpenses;

        /*
        |--------------------------------------------------------------
        | OUTSTANDING (vouchers still unpaid/partial whose period is this month)
        |--------------------------------------------------------------
        */
        $outstandingVouchers = FeeVoucher::with(['student.activeEnrollment.class'])
            ->whereBetween('period_from', [$monthStart, $monthEnd])
            ->whereIn('status', ['unpaid','partial'])
            ->orderByDesc('balance_amount')
            ->get();

        $totalOutstanding = $outstandingVouchers->sum('balance_amount');
        $totalBilled      = FeeVoucher::whereBetween('period_from', [$monthStart, $monthEnd])
            ->sum('payable_amount');
        $recoveryPct = $totalBilled > 0
            ? round(($totalIncome / $totalBilled) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------
        | 6-MONTH TREND (for mini chart data)
        |--------------------------------------------------------------
        */
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m  = date('Y-m', strtotime("-{$i} months", strtotime($monthStart)));
            $mS = $m . '-01';
            $mE = date('Y-m-t', strtotime($mS));
            $trend[] = [
                'label'    => date('M', strtotime($mS)),
                'income'   => (float) FeePayment::whereBetween('payment_date',[$mS,$mE])->sum('amount_paid'),
                'expenses' => (float) Expense::where('expense_month',$m)->sum('amount'),
            ];
        }

        return view('monthly_ledger.index', compact(
            'month','monthStart','monthEnd',
            'payments','incomeByMethod','totalIncome',
            'expenses','expenseByCategory','totalExpenses',
            'netBalance',
            'outstandingVouchers','totalOutstanding','totalBilled','recoveryPct',
            'trend'
        ));
    }
}

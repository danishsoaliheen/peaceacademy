<?php
namespace App\Http\Controllers;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $month    = $request->input('month', now()->format('Y-m'));
        $category = $request->input('category');

        $query = Expense::orderByDesc('expense_date')->orderByDesc('id');
        if ($month)    $query->where('expense_month', $month);
        if ($category) $query->where('category', $category);

        $expenses = $query->paginate(30)->withQueryString();

        $summary = Expense::where('expense_month', $month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total','category');

        $monthTotal = $summary->sum();

        return view('expenses.index', compact('expenses','month','category','summary','monthTotal'));
    }

    public function create()
    {
        $categories = Expense::categories();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category'       => 'required|string|max:80',
            'sub_category'   => 'nullable|string|max:80',
            'description'    => 'nullable|string|max:255',
            'amount'         => 'required|numeric|min:1',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|string|max:40',
            'paid_to'        => 'nullable|string|max:100',
            'reference_no'   => 'nullable|string|max:60',
            'recorded_by'    => 'nullable|string|max:80',
            'notes'          => 'nullable|string',
        ]);
        $data['expense_no']    = $this->nextNo();
        $data['expense_month'] = substr($data['expense_date'], 0, 7);
        Expense::create($data);
        return redirect()->route('expenses.index')->with('success','Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::categories();
        return view('expenses.edit', compact('expense','categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'category'       => 'required|string|max:80',
            'sub_category'   => 'nullable|string|max:80',
            'description'    => 'nullable|string|max:255',
            'amount'         => 'required|numeric|min:1',
            'expense_date'   => 'required|date',
            'payment_method' => 'required|string|max:40',
            'paid_to'        => 'nullable|string|max:100',
            'reference_no'   => 'nullable|string|max:60',
            'recorded_by'    => 'nullable|string|max:80',
            'notes'          => 'nullable|string',
        ]);
        $data['expense_month'] = substr($data['expense_date'], 0, 7);
        $expense->update($data);
        return redirect()->route('expenses.index')->with('success','Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success','Expense deleted.');
    }

    private function nextNo(): string
    {
        $year = now()->format('Y');
        $last = Expense::where('expense_no','like',"EXP-{$year}-%")
            ->orderByDesc('id')->value('expense_no');
        $seq  = $last ? ((int) substr($last,-4)) + 1 : 1;
        return sprintf('EXP-%s-%04d', $year, $seq);
    }
}

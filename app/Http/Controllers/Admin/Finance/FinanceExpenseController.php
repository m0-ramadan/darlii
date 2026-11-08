<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceExpense;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinanceExpenseController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $query = FinanceExpense::query()
            ->with('creator')
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->input('q'));
                $q->where(fn ($x) => $x->where('title', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%"));
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->input('method')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->input('to')))
            ->latest('expense_date')->latest('id');

        $totalAmount = (float) (clone $query)->sum('amount');
        $expenses = $query->paginate(20)->withQueryString();

        return view('admin.finance.expenses.index', [
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
            'categories' => FinanceExpense::CATEGORIES,
            'methods' => FinanceExpense::METHODS,
            'filters' => $request->only(['q', 'category', 'method', 'from', 'to']),
            'title' => 'المصروفات',
            'subtitle' => 'تسجيل ومتابعة مصروفات المنصة',
        ]);
    }

    public function create()
    {
        return view('admin.finance.expenses.form', [
            'expense' => new FinanceExpense(['category' => 'other', 'method' => 'cash', 'currency' => 'EGP', 'expense_date' => now()]),
            'categories' => FinanceExpense::CATEGORIES,
            'methods' => FinanceExpense::METHODS,
            'title' => 'إضافة مصروف',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $this->admin()->id;
        $expense = FinanceExpense::create($data);
        AuditLogger::log('finance.expense.created', $expense, [], $expense->only(['title', 'category', 'amount', 'expense_date']));

        return redirect()->route('admin.finance.expenses.index')->with('success', 'تم تسجيل المصروف.');
    }

    public function edit(FinanceExpense $expense)
    {
        return view('admin.finance.expenses.form', [
            'expense' => $expense,
            'categories' => FinanceExpense::CATEGORIES,
            'methods' => FinanceExpense::METHODS,
            'title' => 'تعديل مصروف',
        ]);
    }

    public function update(Request $request, FinanceExpense $expense)
    {
        $data = $this->validated($request);
        $old = $expense->only(['title', 'category', 'amount', 'expense_date']);
        $expense->update($data);
        AuditLogger::log('finance.expense.updated', $expense, $old, $expense->fresh()->only(array_keys($old)));

        return redirect()->route('admin.finance.expenses.index')->with('success', 'تم تحديث المصروف.');
    }

    public function destroy(FinanceExpense $expense)
    {
        AuditLogger::log('finance.expense.deleted', $expense, $expense->only(['title', 'amount', 'expense_date']));
        $expense->delete();

        return back()->with('success', 'تم حذف المصروف.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', Rule::in(array_keys(FinanceExpense::CATEGORIES))],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            'currency' => ['required', 'in:EGP'],
            'method' => ['required', Rule::in(array_keys(FinanceExpense::METHODS))],
            'reference' => ['nullable', 'string', 'max:100'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return [
            'title' => $data['title'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'currency' => 'EGP',
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'expense_date' => $data['expense_date'],
            'notes' => $data['notes'] ?? null,
        ];
    }
}

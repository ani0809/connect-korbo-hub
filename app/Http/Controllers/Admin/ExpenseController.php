<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $expenses = Expense::query()
            ->with(['category', 'creator'])
            ->filter($request->only(['category_id', 'payment_method', 'date_from', 'date_to', 'search']))
            ->latest()
            ->paginate(20);

        $stats = [
            'this_month' => (float) Expense::query()->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total'),
            'last_month' => (float) Expense::query()->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->sum('total'),
            'ytd' => (float) Expense::query()->whereYear('date', now()->year)->sum('total'),
            'by_category' => Expense::query()->with('category')->whereMonth('date', now()->month)->select('category_id')->selectRaw('SUM(total) as total')->groupBy('category_id')->get(),
        ];

        $categories = ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get();
        return view('admin.accounting.expenses.index', compact('expenses', 'stats', 'categories'));
    }

    public function create(): View
    {
        $categories = ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get();
        $paymentAccounts = Account::query()->where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
        return view('admin.accounting.expenses.create', compact('categories', 'paymentAccounts'));
    }

    public function store(Request $request, AccountingService $accounting): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'date' => 'required|date',
            'description' => 'required|string',
            'payment_method' => 'required|in:cash,bank,mobile,card',
            'payment_account_id' => 'nullable|exists:accounts,id',
            'reference' => 'nullable|string|max:100',
            'attachment' => 'nullable|image|max:5120',
            'is_recurring' => 'nullable|boolean',
            'recurring_interval' => 'nullable|string|max:30',
            'next_due_date' => 'nullable|date',
        ]);

        $number = 'EXP-'.now()->format('Ymd').'-'.str_pad((string) (Expense::query()->whereDate('created_at', today())->count() + 1), 3, '0', STR_PAD_LEFT);
        $category = ExpenseCategory::query()->with('account')->findOrFail((int) $data['category_id']);
        $attachmentPath = $request->hasFile('attachment') ? $request->file('attachment')->store('expenses', 'public') : null;
        $amount = (float) $data['amount'];
        $taxAmount = (float) ($data['tax_amount'] ?? 0);

        $expense = Expense::query()->create([
            'expense_number' => $number,
            'category_id' => $category->id,
            'account_id' => $category->account_id ?? (int) Account::query()->where('code', '6000')->value('id'),
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total' => $amount + $taxAmount,
            'date' => $data['date'],
            'description' => $data['description'],
            'payment_method' => $data['payment_method'],
            'payment_account_id' => $data['payment_account_id'] ?? null,
            'reference' => $data['reference'] ?? null,
            'attachment' => $attachmentPath,
            'is_recurring' => (bool) ($data['is_recurring'] ?? false),
            'recurring_interval' => $data['recurring_interval'] ?? null,
            'next_due_date' => $data['next_due_date'] ?? null,
            'created_by' => auth()->id() ?? 1,
        ]);

        if ((bool) setting('accounting_auto_journal', true)) {
            $journal = $accounting->recordExpense($expense);
            $expense->update(['journal_id' => $journal->id]);
        }

        return redirect()->route('admin.accounting.expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(int $id): View
    {
        $expense = Expense::query()->findOrFail($id);
        $categories = ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get();
        $paymentAccounts = Account::query()->where('type', 'asset')->where('is_active', true)->orderBy('code')->get();
        return view('admin.accounting.expenses.create', compact('expense', 'categories', 'paymentAccounts'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $expense = Expense::query()->findOrFail($id);
        $data = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'date' => 'required|date',
            'description' => 'required|string',
            'payment_method' => 'required|in:cash,bank,mobile,card',
            'payment_account_id' => 'nullable|exists:accounts,id',
            'reference' => 'nullable|string|max:100',
            'is_recurring' => 'nullable|boolean',
            'recurring_interval' => 'nullable|string|max:30',
            'next_due_date' => 'nullable|date',
        ]);
        $amount = (float) $data['amount'];
        $taxAmount = (float) ($data['tax_amount'] ?? 0);
        $category = ExpenseCategory::query()->findOrFail((int) $data['category_id']);

        $expense->update([
            'category_id' => $category->id,
            'account_id' => $category->account_id ?? $expense->account_id,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total' => $amount + $taxAmount,
            'date' => $data['date'],
            'description' => $data['description'],
            'payment_method' => $data['payment_method'],
            'payment_account_id' => $data['payment_account_id'] ?? null,
            'reference' => $data['reference'] ?? null,
            'is_recurring' => (bool) ($data['is_recurring'] ?? false),
            'recurring_interval' => $data['recurring_interval'] ?? null,
            'next_due_date' => $data['next_due_date'] ?? null,
        ]);

        return redirect()->route('admin.accounting.expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $expense = Expense::query()->findOrFail($id);
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }
}


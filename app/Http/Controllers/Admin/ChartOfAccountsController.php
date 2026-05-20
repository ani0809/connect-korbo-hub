<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChartOfAccountsController extends Controller
{
    public function index(): View
    {
        $accounts = Account::query()->with('children')->orderBy('code')->get();
        $parents = Account::query()->whereNull('parent_id')->orderBy('code')->get();
        return view('admin.accounting.chart-of-accounts', compact('accounts', 'parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code',
            'name' => 'required|string|max:191',
            'type' => 'required|in:asset,liability,equity,income,expense,cost_of_goods',
            'subtype' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        Account::query()->create([
            ...$data,
            'is_system' => false,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'balance' => 0,
        ]);

        return back()->with('success', 'Account added.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $account = Account::query()->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'type' => 'required|in:asset,liability,equity,income,expense,cost_of_goods',
            'subtype' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($account->is_system && $account->type !== $data['type']) {
            return back()->withErrors(['type' => 'System account type cannot be changed.']);
        }

        $account->update([
            'name' => $data['name'],
            'type' => $account->is_system ? $account->type : $data['type'],
            'subtype' => $data['subtype'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('success', 'Account updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $account = Account::query()->withCount('journalLines')->findOrFail($id);
        if ($account->is_system) {
            return back()->withErrors(['account' => 'System account cannot be deleted.']);
        }
        if ($account->journal_lines_count > 0) {
            return back()->withErrors(['account' => 'Account with journal activity cannot be deleted.']);
        }
        $account->delete();
        return back()->with('success', 'Account deleted.');
    }
}


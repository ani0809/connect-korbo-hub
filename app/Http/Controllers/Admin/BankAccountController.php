<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\BankAccount;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function index(): View
    {
        $accounts = BankAccount::query()->with('chartAccount')->orderBy('id')->get();
        return view('admin.accounting.bank-accounts', compact('accounts'));
    }

    public function store(Request $request, AccountingService $accounting): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:191',
            'account_type' => 'required|in:checking,savings,mobile_banking,cash',
            'opening_balance' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data, $accounting): void {
            $accountCode = '1'.str_pad((string) (BankAccount::query()->count() + 2), 3, '0', STR_PAD_LEFT);
            $parentId = Account::query()->where('code', '1000')->value('id');
            $opening = (float) ($data['opening_balance'] ?? 0);

            $chartAccount = Account::query()->create([
                'code' => $accountCode,
                'name' => (($data['bank_name'] ?? 'Bank').' - '.$data['name']),
                'type' => 'asset',
                'subtype' => 'current_asset',
                'parent_id' => $parentId,
                'is_system' => false,
                'is_active' => true,
                'balance' => $opening,
            ]);

            if (! empty($data['is_default'])) {
                BankAccount::query()->update(['is_default' => false]);
            }

            $bank = BankAccount::query()->create([
                'name' => $data['name'],
                'account_number' => $data['account_number'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'account_type' => $data['account_type'],
                'account_id' => $chartAccount->id,
                'opening_balance' => $opening,
                'current_balance' => $opening,
                'currency' => $data['currency'] ?? 'BDT',
                'is_default' => (bool) ($data['is_default'] ?? false),
                'is_active' => true,
            ]);

            if ($opening > 0) {
                $equityId = (int) Account::query()->where('code', '3001')->value('id');
                $accounting->createJournal(
                    'Opening balance - '.$bank->name,
                    [
                        ['account_id' => $chartAccount->id, 'debit' => $opening, 'credit' => 0],
                        ['account_id' => $equityId, 'debit' => 0, 'credit' => $opening],
                    ],
                    now()->toDateString(),
                    'Manual',
                    null
                );
            }
        });

        return back()->with('success', 'Bank account created.');
    }

    public function updateBalance(Request $request, int $id, AccountingService $accounting): RedirectResponse
    {
        $data = $request->validate(['balance' => 'required|numeric|min:0']);
        $bank = BankAccount::query()->findOrFail($id);
        $newBalance = (float) $data['balance'];
        $difference = $newBalance - (float) $bank->current_balance;

        if (abs($difference) > 0.009) {
            $drawingsId = (int) Account::query()->where('code', '3002')->value('id');
            $accounting->createJournal(
                'Balance adjustment - '.$bank->name,
                [
                    [
                        'account_id' => $bank->account_id,
                        'debit' => $difference > 0 ? $difference : 0,
                        'credit' => $difference < 0 ? abs($difference) : 0,
                    ],
                    [
                        'account_id' => $drawingsId,
                        'debit' => $difference < 0 ? abs($difference) : 0,
                        'credit' => $difference > 0 ? $difference : 0,
                    ],
                ],
                now()->toDateString(),
                'Manual',
                null
            );
            $bank->update(['current_balance' => $newBalance]);
        }

        return back()->with('success', 'Balance updated.');
    }
}


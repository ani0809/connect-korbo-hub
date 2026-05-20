<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\Journal;
use App\Models\JournalLine;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function createJournal(string $description, array $lines, ?string $date = null, ?string $refType = null, ?int $refId = null): Journal
    {
        $totalDebit = (float) collect($lines)->sum(fn ($l) => (float) ($l['debit'] ?? 0));
        $totalCredit = (float) collect($lines)->sum(fn ($l) => (float) ($l['credit'] ?? 0));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new \RuntimeException("Journal not balanced: Debit {$totalDebit} != Credit {$totalCredit}");
        }

        return DB::transaction(function () use ($description, $lines, $date, $refType, $refId): Journal {
            $count = Journal::query()->whereYear('created_at', now()->year)->count() + 1;
            $number = 'JE-'.now()->format('Y').'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);

            $journal = Journal::query()->create([
                'journal_number' => $number,
                'date' => $date ?? now()->toDateString(),
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'status' => 'posted',
                'created_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($lines as $line) {
                JournalLine::query()->create([
                    'journal_id' => $journal->id,
                    'account_id' => (int) $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);

                $account = Account::query()->find((int) $line['account_id']);
                if ($account) {
                    $change = $this->calculateBalanceChange(
                        (string) $account->type,
                        (float) ($line['debit'] ?? 0),
                        (float) ($line['credit'] ?? 0),
                    );
                    $account->increment('balance', $change);
                }
            }

            return $journal;
        });
    }

    public function recordSale(Order $order): Journal
    {
        $accountsReceivable = $this->getAccount('1101');
        $salesRevenue = $this->getAccount('4001');
        $shippingRevenue = $this->getAccount('4002');
        $vatPayable = $this->getAccount('2101');
        $cashAccount = $this->getCashAccount((string) $order->payment_method);

        $lines = [];
        $lines[] = [
            'account_id' => $order->payment_status === 'paid' ? $cashAccount->id : $accountsReceivable->id,
            'debit' => (float) $order->total,
            'credit' => 0,
            'description' => ($order->payment_status === 'paid' ? 'Payment received - ' : 'Receivable - ').$order->order_number,
        ];
        $lines[] = [
            'account_id' => $salesRevenue->id,
            'debit' => 0,
            'credit' => (float) $order->subtotal,
            'description' => 'Sales - '.$order->order_number,
        ];

        if ((float) $order->shipping_cost > 0) {
            $lines[] = [
                'account_id' => $shippingRevenue->id,
                'debit' => 0,
                'credit' => (float) $order->shipping_cost,
                'description' => 'Shipping revenue - '.$order->order_number,
            ];
        }
        if ((float) $order->tax_amount > 0) {
            $lines[] = [
                'account_id' => $vatPayable->id,
                'debit' => 0,
                'credit' => (float) $order->tax_amount,
                'description' => 'VAT payable - '.$order->order_number,
            ];
        }

        return $this->createJournal(
            'Sale: '.$order->order_number,
            $lines,
            $order->created_at?->toDateString(),
            'Order',
            (int) $order->id
        );
    }

    public function reverseSale(Order $order, string $reason = 'Order cancelled'): Journal
    {
        $journal = Journal::query()
            ->where('reference_type', 'Order')
            ->where('reference_id', $order->id)
            ->where('status', 'posted')
            ->with('lines')
            ->latest('id')
            ->first();

        if (! $journal) {
            throw new \RuntimeException('No posted sale journal found to reverse.');
        }

        $reverseLines = $journal->lines->map(fn (JournalLine $line) => [
            'account_id' => $line->account_id,
            'debit' => (float) $line->credit,
            'credit' => (float) $line->debit,
            'description' => 'REVERSAL: '.$reason,
        ])->all();

        return $this->createJournal(
            'Reverse Sale: '.$order->order_number.' ('.$reason.')',
            $reverseLines,
            now()->toDateString(),
            'OrderCancel',
            (int) $order->id
        );
    }

    public function recordExpense(Expense $expense): Journal
    {
        $paymentAccount = $expense->payment_account_id
            ? Account::query()->find($expense->payment_account_id)
            : $this->getAccount('1001');
        $paymentAccount ??= $this->getAccount('1001');

        return $this->createJournal(
            'Expense: '.$expense->description,
            [
                [
                    'account_id' => (int) $expense->account_id,
                    'debit' => (float) $expense->amount,
                    'credit' => 0,
                    'description' => $expense->description,
                ],
                [
                    'account_id' => (int) $paymentAccount->id,
                    'debit' => 0,
                    'credit' => (float) $expense->total,
                    'description' => 'Payment for expense',
                ],
            ],
            $expense->date?->toDateString(),
            'Expense',
            (int) $expense->id
        );
    }

    public function getProfitAndLoss(string $fromDate, string $toDate): array
    {
        $income = $this->getAccountTotals('income', $fromDate, $toDate);
        $cogs = $this->getAccountTotals('cost_of_goods', $fromDate, $toDate);
        $expenses = $this->getAccountTotals('expense', $fromDate, $toDate);
        $grossProfit = $income['total'] - $cogs['total'];
        $netProfit = $grossProfit - $expenses['total'];

        return [
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'income' => $income,
            'cost_of_goods' => $cogs,
            'gross_profit' => $grossProfit,
            'gross_margin' => $income['total'] > 0 ? ($grossProfit / $income['total']) * 100 : 0,
            'expenses' => $expenses,
            'net_profit' => $netProfit,
            'net_margin' => $income['total'] > 0 ? ($netProfit / $income['total']) * 100 : 0,
            'generated_at' => now()->format('Y-m-d H:i'),
        ];
    }

    public function getBalanceSheet(string $asOfDate): array
    {
        $assets = $this->getAccountBalances('asset', $asOfDate);
        $liabilities = $this->getAccountBalances('liability', $asOfDate);
        $equity = $this->getAccountBalances('equity', $asOfDate);
        $ytdProfit = $this->getProfitAndLoss(now()->startOfYear()->toDateString(), $asOfDate)['net_profit'];

        $totalAssets = $assets['total'];
        $totalLiabilities = $liabilities['total'];
        $totalEquity = $equity['total'] + $ytdProfit;

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => [
                'items' => $equity['items'],
                'ytd_profit' => $ytdProfit,
                'total' => $totalEquity,
            ],
            'check' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    public function getCashFlow(string $fromDate, string $toDate): array
    {
        $salesCash = $this->getCashFromAccountCode('4001', $fromDate, $toDate) + $this->getCashFromAccountCode('4002', $fromDate, $toDate);
        $expenseCash = $this->getCashToType('expense', $fromDate, $toDate);
        $cogsCash = $this->getCashToType('cost_of_goods', $fromDate, $toDate);
        $assetPurchases = $this->getCashToCodes(['1400', '1401', '1402', '1403'], $fromDate, $toDate);
        $ownerDeposits = $this->getCashFromAccountCode('3001', $fromDate, $toDate);
        $ownerDrawings = $this->getCashToCodes(['3002'], $fromDate, $toDate);

        $operating = $salesCash - $expenseCash - $cogsCash;
        $investing = -$assetPurchases;
        $financing = $ownerDeposits - $ownerDrawings;

        return [
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'operating' => [
                'sales_receipts' => $salesCash,
                'expense_payments' => $expenseCash,
                'cogs_payments' => $cogsCash,
                'net' => $operating,
            ],
            'investing' => ['asset_purchases' => $assetPurchases, 'net' => $investing],
            'financing' => ['owner_deposits' => $ownerDeposits, 'owner_drawings' => $ownerDrawings, 'net' => $financing],
            'net_cash_flow' => $operating + $investing + $financing,
        ];
    }

    public function getTrialBalance(string $asOfDate): array
    {
        $accounts = Account::query()
            ->where('is_active', true)
            ->whereHas('journalLines', function ($q) use ($asOfDate): void {
                $q->whereHas('journal', function ($q2) use ($asOfDate): void {
                    $q2->where('date', '<=', $asOfDate)->where('status', 'posted');
                });
            })
            ->orderBy('code')
            ->get();

        $items = $accounts->map(fn (Account $a) => [
            'code' => $a->code,
            'name' => $a->name,
            'type' => $a->type,
            'debit' => $this->getAccountDebits((int) $a->id, $asOfDate),
            'credit' => $this->getAccountCredits((int) $a->id, $asOfDate),
        ]);

        $debit = (float) $items->sum('debit');
        $credit = (float) $items->sum('credit');

        return [
            'as_of_date' => $asOfDate,
            'items' => $items,
            'total_debit' => $debit,
            'total_credit' => $credit,
            'is_balanced' => abs($debit - $credit) < 0.01,
        ];
    }

    private function getAccountTotals(string $type, string $fromDate, string $toDate): array
    {
        $accounts = Account::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('code')
            ->get();

        $items = [];
        $total = 0.0;
        foreach ($accounts as $account) {
            $amount = $this->getAccountBalance((int) $account->id, $fromDate, $toDate);
            foreach ($account->children as $child) {
                $amount += $this->getAccountBalance((int) $child->id, $fromDate, $toDate);
            }
            if (abs($amount) > 0.009) {
                $items[] = ['code' => $account->code, 'name' => $account->name, 'amount' => $amount];
                $total += $amount;
            }
        }

        return ['items' => $items, 'total' => $total];
    }

    private function getAccountBalance(int $accountId, string $fromDate, string $toDate): float
    {
        return (float) JournalLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$fromDate, $toDate])->where('status', 'posted'))
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit),0) as balance')
            ->value('balance');
    }

    private function getAccountDebits(int $accountId, string $toDate): float
    {
        return (float) JournalLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journal', fn ($q) => $q->where('date', '<=', $toDate)->where('status', 'posted'))
            ->sum('debit');
    }

    private function getAccountCredits(int $accountId, string $toDate): float
    {
        return (float) JournalLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journal', fn ($q) => $q->where('date', '<=', $toDate)->where('status', 'posted'))
            ->sum('credit');
    }

    private function getAccountBalances(string $type, string $asOfDate): array
    {
        $accounts = Account::query()->where('type', $type)->where('is_active', true)->whereNull('parent_id')->with('children')->orderBy('code')->get();
        $items = [];
        $total = 0.0;

        foreach ($accounts as $account) {
            $balance = $this->getAccountDebits((int) $account->id, $asOfDate) - $this->getAccountCredits((int) $account->id, $asOfDate);
            foreach ($account->children as $child) {
                $balance += $this->getAccountDebits((int) $child->id, $asOfDate) - $this->getAccountCredits((int) $child->id, $asOfDate);
            }
            if (in_array($type, ['liability', 'equity', 'income'], true)) {
                $balance *= -1;
            }
            if (abs($balance) > 0.009) {
                $items[] = ['code' => $account->code, 'name' => $account->name, 'balance' => $balance];
                $total += $balance;
            }
        }

        return ['items' => $items, 'total' => $total];
    }

    private function calculateBalanceChange(string $accountType, float $debit, float $credit): float
    {
        return match ($accountType) {
            'asset', 'expense', 'cost_of_goods' => $debit - $credit,
            'liability', 'equity', 'income' => $credit - $debit,
            default => 0,
        };
    }

    private function getAccount(string $code): Account
    {
        return Account::query()->where('code', $code)->firstOrFail();
    }

    private function getCashAccount(string $paymentMethod): Account
    {
        $accountCode = match ($paymentMethod) {
            'bkash' => '1003',
            'nagad' => '1004',
            'cod' => '1102',
            'stripe', 'card', 'sslcommerz' => '1002',
            default => '1001',
        };
        return $this->getAccount($accountCode);
    }

    private function getCashFromAccountCode(string $code, string $fromDate, string $toDate): float
    {
        $account = Account::query()->where('code', $code)->first();
        if (! $account) {
            return 0;
        }
        return (float) JournalLine::query()
            ->where('account_id', $account->id)
            ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$fromDate, $toDate])->where('status', 'posted'))
            ->sum('credit');
    }

    private function getCashToType(string $type, string $fromDate, string $toDate): float
    {
        $accountIds = Account::query()->where('type', $type)->pluck('id');
        return (float) JournalLine::query()
            ->whereIn('account_id', $accountIds)
            ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$fromDate, $toDate])->where('status', 'posted'))
            ->sum('debit');
    }

    /**
     * @param  array<int, string>  $codes
     */
    private function getCashToCodes(array $codes, string $fromDate, string $toDate): float
    {
        $accountIds = Account::query()->whereIn('code', $codes)->pluck('id');
        return (float) JournalLine::query()
            ->whereIn('account_id', $accountIds)
            ->whereHas('journal', fn ($q) => $q->whereBetween('date', [$fromDate, $toDate])->where('status', 'posted'))
            ->sum('debit');
    }
}


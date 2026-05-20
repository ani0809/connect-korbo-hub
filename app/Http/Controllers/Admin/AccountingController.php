<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Journal;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function dashboard(AccountingService $accounting): View
    {
        $fromDate = now()->startOfMonth()->toDateString();
        $toDate = now()->toDateString();

        $pl = $accounting->getProfitAndLoss($fromDate, $toDate);
        $bankBalances = BankAccount::query()->where('is_active', true)->get()->map(fn ($b) => [
            'name' => $b->name,
            'balance' => (float) $b->current_balance,
            'type' => $b->account_type,
        ]);
        $recentJournals = Journal::query()->where('status', 'posted')->latest()->take(10)->with('creator')->get();
        $monthlyRevenue = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, SUM(total) as revenue, SUM(subtotal) as subtotal, COUNT(*) as orders')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $monthlyExpenses = DB::table('expenses')
            ->whereYear('date', now()->year)
            ->selectRaw('MONTH(date) as month, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.accounting.dashboard', compact('pl', 'bankBalances', 'recentJournals', 'monthlyRevenue', 'monthlyExpenses'));
    }

    public function profitAndLoss(AccountingService $accounting)
    {
        $fromDate = request('from', now()->startOfMonth()->toDateString());
        $toDate = request('to', now()->toDateString());
        $report = $accounting->getProfitAndLoss($fromDate, $toDate);
        if (request('export') === 'pdf') {
            $pdf = app('pdf')->loadView('admin.accounting.reports.pl-pdf', compact('report'));
            return $pdf->download('profit-loss-'.$fromDate.'-'.$toDate.'.pdf');
        }
        return view('admin.accounting.reports.profit-loss', compact('report', 'fromDate', 'toDate'));
    }

    public function balanceSheet(AccountingService $accounting): View
    {
        $asOfDate = request('date', now()->toDateString());
        $report = $accounting->getBalanceSheet($asOfDate);
        return view('admin.accounting.reports.balance-sheet', compact('report', 'asOfDate'));
    }

    public function trialBalance(AccountingService $accounting): View
    {
        $asOfDate = request('date', now()->toDateString());
        $report = $accounting->getTrialBalance($asOfDate);
        return view('admin.accounting.reports.trial-balance', compact('report', 'asOfDate'));
    }

    public function cashFlow(AccountingService $accounting): View
    {
        $fromDate = request('from', now()->startOfMonth()->toDateString());
        $toDate = request('to', now()->toDateString());
        $report = $accounting->getCashFlow($fromDate, $toDate);
        return view('admin.accounting.reports.cash-flow', compact('report', 'fromDate', 'toDate'));
    }

    public function taxReport(): View
    {
        $fromDate = request('from', now()->startOfQuarter()->toDateString());
        $toDate = request('to', now()->toDateString());

        $salesTax = (float) DB::table('orders')->where('payment_status', 'paid')->whereBetween('created_at', [$fromDate, $toDate])->sum('tax_amount');
        $purchaseTax = (float) DB::table('expenses')->whereBetween('date', [$fromDate, $toDate])->sum('tax_amount');
        $netTaxPayable = $salesTax - $purchaseTax;
        $taxByMonth = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('MONTH(created_at) as month, SUM(tax_amount) as sales_tax')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.accounting.reports.tax', compact('salesTax', 'purchaseTax', 'netTaxPayable', 'taxByMonth', 'fromDate', 'toDate'));
    }
}


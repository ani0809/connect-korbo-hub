@extends('admin.layouts.app')
@section('title', 'Accounting Dashboard')
@section('content')
<div class="space-y-4">
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="card p-4"><div class="text-xs text-slate-500">Revenue</div><div class="text-xl font-bold">{{ currency_format($pl['income']['total']) }}</div></div>
    <div class="card p-4"><div class="text-xs text-slate-500">Expenses</div><div class="text-xl font-bold">{{ currency_format($pl['expenses']['total']) }}</div></div>
    <div class="card p-4"><div class="text-xs text-slate-500">Net Profit</div><div class="text-xl font-bold {{ $pl['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ currency_format($pl['net_profit']) }}</div></div>
    <div class="card p-4"><div class="text-xs text-slate-500">Profit Margin</div><div class="text-xl font-bold {{ $pl['net_margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($pl['net_margin'], 2) }}%</div></div>
  </div>

  <div class="card p-4">
    <div class="font-semibold mb-3">Bank Balances</div>
    <div class="flex flex-wrap gap-2">
      @foreach($bankBalances as $b)
        <span class="px-3 py-2 rounded-lg bg-slate-100 text-sm">🏦 {{ $b['name'] }}: <strong>{{ currency_format($b['balance']) }}</strong></span>
      @endforeach
    </div>
  </div>

  <div class="card p-4">
    <div class="font-semibold mb-3">Quick Links</div>
    <div class="flex flex-wrap gap-2 text-sm">
      <a class="btn-secondary" href="{{ route('admin.accounting.reports.profit-loss') }}">P&L Report</a>
      <a class="btn-secondary" href="{{ route('admin.accounting.reports.balance-sheet') }}">Balance Sheet</a>
      <a class="btn-secondary" href="{{ route('admin.accounting.reports.trial-balance') }}">Trial Balance</a>
      <a class="btn-secondary" href="{{ route('admin.accounting.reports.tax') }}">Tax Report</a>
      <a class="btn-secondary" href="{{ route('admin.accounting.expenses.create') }}">Record Expense</a>
      <a class="btn-secondary" href="{{ route('admin.accounting.journals.index') }}">Journal Entries</a>
    </div>
  </div>

  <div class="card overflow-x-auto">
    <div class="p-4 border-b font-semibold">Recent Journal Entries</div>
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">JE #</th><th class="p-3 text-left">Date</th><th class="p-3 text-left">Description</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Created By</th></tr></thead>
      <tbody>
        @foreach($recentJournals as $j)
        <tr class="border-t"><td class="p-3 font-mono">{{ $j->journal_number }}</td><td class="p-3">{{ $j->date?->format('Y-m-d') }}</td><td class="p-3">{{ $j->description }}</td><td class="p-3 uppercase">{{ $j->status }}</td><td class="p-3">{{ $j->creator?->name }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection


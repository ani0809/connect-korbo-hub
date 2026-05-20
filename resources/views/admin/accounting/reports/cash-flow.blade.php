@extends('admin.layouts.app')
@section('title', 'Cash Flow')
@section('content')
<div class="space-y-4">
  <form class="bg-white border rounded-xl p-3 flex flex-wrap gap-2">
    <input type="date" name="from" value="{{ $fromDate }}" class="border rounded px-3 py-2 text-sm">
    <input type="date" name="to" value="{{ $toDate }}" class="border rounded px-3 py-2 text-sm">
    <button class="btn-secondary text-sm">Generate</button>
  </form>

  <div class="grid md:grid-cols-3 gap-3">
    <div class="bg-white border rounded-xl p-4">
      <div class="font-semibold mb-2">Operating</div>
      <div class="text-sm flex justify-between"><span>Sales Receipts</span><span>{{ currency_format($report['operating']['sales_receipts']) }}</span></div>
      <div class="text-sm flex justify-between"><span>Expense Payments</span><span>{{ currency_format($report['operating']['expense_payments']) }}</span></div>
      <div class="text-sm flex justify-between"><span>COGS Payments</span><span>{{ currency_format($report['operating']['cogs_payments']) }}</span></div>
      <div class="mt-2 border-t pt-2 font-semibold flex justify-between"><span>Net</span><span>{{ currency_format($report['operating']['net']) }}</span></div>
    </div>
    <div class="bg-white border rounded-xl p-4">
      <div class="font-semibold mb-2">Investing</div>
      <div class="text-sm flex justify-between"><span>Asset Purchases</span><span>{{ currency_format($report['investing']['asset_purchases']) }}</span></div>
      <div class="mt-2 border-t pt-2 font-semibold flex justify-between"><span>Net</span><span>{{ currency_format($report['investing']['net']) }}</span></div>
    </div>
    <div class="bg-white border rounded-xl p-4">
      <div class="font-semibold mb-2">Financing</div>
      <div class="text-sm flex justify-between"><span>Owner Deposits</span><span>{{ currency_format($report['financing']['owner_deposits']) }}</span></div>
      <div class="text-sm flex justify-between"><span>Owner Drawings</span><span>{{ currency_format($report['financing']['owner_drawings']) }}</span></div>
      <div class="mt-2 border-t pt-2 font-semibold flex justify-between"><span>Net</span><span>{{ currency_format($report['financing']['net']) }}</span></div>
    </div>
  </div>

  <div class="bg-white border rounded-xl p-4 text-lg font-bold flex justify-between">
    <span>Net Cash Flow</span><span>{{ currency_format($report['net_cash_flow']) }}</span>
  </div>
</div>
@endsection


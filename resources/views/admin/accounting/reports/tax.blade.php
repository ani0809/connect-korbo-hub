@extends('admin.layouts.app')
@section('title', 'VAT & Tax Report')
@section('content')
<div class="space-y-4">
  <form class="bg-white border rounded-xl p-3 flex flex-wrap gap-2">
    <input type="date" name="from" value="{{ $fromDate }}" class="border rounded px-3 py-2 text-sm">
    <input type="date" name="to" value="{{ $toDate }}" class="border rounded px-3 py-2 text-sm">
    <button class="btn-secondary text-sm">Generate</button>
  </form>

  <div class="grid md:grid-cols-3 gap-3">
    <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">Sales Tax Collected</div><div class="text-xl font-bold">{{ currency_format($salesTax) }}</div></div>
    <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">Input Tax (Purchases)</div><div class="text-xl font-bold">{{ currency_format($purchaseTax) }}</div></div>
    <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">Net Tax Payable</div><div class="text-xl font-bold {{ $netTaxPayable >= 0 ? 'text-red-600' : 'text-green-600' }}">{{ currency_format($netTaxPayable) }}</div></div>
  </div>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">Month</th><th class="p-3 text-left">Sales Tax</th></tr></thead>
      <tbody>
        @foreach($taxByMonth as $row)
          <tr class="border-t"><td class="p-3">{{ \Carbon\Carbon::create()->month((int)$row->month)->format('F') }}</td><td class="p-3">{{ currency_format($row->sales_tax) }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection


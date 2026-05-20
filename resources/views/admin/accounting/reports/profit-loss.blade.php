@extends('admin.layouts.app')
@section('title', 'Profit & Loss')
@section('content')
<div class="space-y-4">
  <form class="bg-white border rounded-xl p-3 flex flex-wrap gap-2">
    <input type="date" name="from" value="{{ $fromDate }}" class="border rounded px-3 py-2 text-sm">
    <input type="date" name="to" value="{{ $toDate }}" class="border rounded px-3 py-2 text-sm">
    <button class="btn-secondary text-sm">Generate</button>
  </form>

  <div class="bg-white border rounded-xl p-4">
    <h2 class="text-lg font-bold">{{ setting('site_name') }}</h2>
    <div class="text-sm text-slate-500 mb-4">Profit & Loss Statement ({{ $report['period']['from'] }} to {{ $report['period']['to'] }})</div>
    <div class="grid md:grid-cols-3 gap-4">
      <div><div class="font-semibold mb-2">Income</div>@foreach($report['income']['items'] as $i)<div class="flex justify-between text-sm"><span>{{ $i['name'] }}</span><span>{{ currency_format($i['amount']) }}</span></div>@endforeach<div class="mt-2 pt-2 border-t flex justify-between font-semibold"><span>Total Income</span><span>{{ currency_format($report['income']['total']) }}</span></div></div>
      <div><div class="font-semibold mb-2">COGS</div>@foreach($report['cost_of_goods']['items'] as $i)<div class="flex justify-between text-sm"><span>{{ $i['name'] }}</span><span>{{ currency_format($i['amount']) }}</span></div>@endforeach<div class="mt-2 pt-2 border-t flex justify-between font-semibold"><span>Total COGS</span><span>{{ currency_format($report['cost_of_goods']['total']) }}</span></div></div>
      <div><div class="font-semibold mb-2">Expenses</div>@foreach($report['expenses']['items'] as $i)<div class="flex justify-between text-sm"><span>{{ $i['name'] }}</span><span>{{ currency_format($i['amount']) }}</span></div>@endforeach<div class="mt-2 pt-2 border-t flex justify-between font-semibold"><span>Total Expenses</span><span>{{ currency_format($report['expenses']['total']) }}</span></div></div>
    </div>
    <div class="grid md:grid-cols-2 gap-3 mt-5">
      <div class="p-3 rounded-lg bg-slate-50 border"><div class="text-sm">Gross Profit</div><div class="font-bold">{{ currency_format($report['gross_profit']) }} ({{ number_format($report['gross_margin'], 2) }}%)</div></div>
      <div class="p-3 rounded-lg border {{ $report['net_profit'] >= 0 ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }}"><div class="text-sm">Net Profit / Loss</div><div class="font-bold">{{ currency_format($report['net_profit']) }} ({{ number_format($report['net_margin'], 2) }}%)</div></div>
    </div>
  </div>
</div>
@endsection


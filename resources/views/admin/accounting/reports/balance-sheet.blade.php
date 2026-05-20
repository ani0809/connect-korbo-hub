@extends('admin.layouts.app')
@section('title', 'Balance Sheet')
@section('content')
<div class="space-y-4">
  <form class="bg-white border rounded-xl p-3 flex gap-2 items-end">
    <div><label class="text-xs text-slate-500">As of</label><input type="date" name="date" value="{{ $asOfDate }}" class="border rounded px-3 py-2 text-sm"></div>
    <button class="btn-secondary text-sm">Generate</button>
  </form>

  <div class="grid lg:grid-cols-2 gap-4">
    <div class="bg-white border rounded-xl p-4">
      <h3 class="font-semibold mb-2">Assets</h3>
      @foreach($report['assets']['items'] as $i)<div class="flex justify-between text-sm py-1"><span>{{ $i['name'] }}</span><span>{{ currency_format($i['balance']) }}</span></div>@endforeach
      <div class="mt-2 border-t pt-2 font-semibold flex justify-between"><span>Total Assets</span><span>{{ currency_format($report['assets']['total']) }}</span></div>
    </div>
    <div class="bg-white border rounded-xl p-4">
      <h3 class="font-semibold mb-2">Liabilities + Equity</h3>
      <div class="text-xs uppercase text-slate-500">Liabilities</div>
      @foreach($report['liabilities']['items'] as $i)<div class="flex justify-between text-sm py-1"><span>{{ $i['name'] }}</span><span>{{ currency_format($i['balance']) }}</span></div>@endforeach
      <div class="mt-2 border-t pt-2 font-semibold flex justify-between"><span>Total Liabilities</span><span>{{ currency_format($report['liabilities']['total']) }}</span></div>
      <div class="mt-3 text-xs uppercase text-slate-500">Equity</div>
      @foreach($report['equity']['items'] as $i)<div class="flex justify-between text-sm py-1"><span>{{ $i['name'] }}</span><span>{{ currency_format($i['balance']) }}</span></div>@endforeach
      <div class="flex justify-between text-sm py-1"><span>YTD Profit</span><span>{{ currency_format($report['equity']['ytd_profit']) }}</span></div>
      <div class="mt-2 border-t pt-2 font-semibold flex justify-between"><span>Total Equity</span><span>{{ currency_format($report['equity']['total']) }}</span></div>
    </div>
  </div>
  <div class="p-3 rounded-lg {{ $report['check'] ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
    {{ $report['check'] ? 'Balance Sheet is balanced.' : 'Balance Sheet has discrepancy.' }}
  </div>
</div>
@endsection


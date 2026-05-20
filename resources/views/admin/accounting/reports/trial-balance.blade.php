@extends('admin.layouts.app')
@section('title', 'Trial Balance')
@section('content')
<div class="space-y-4">
  <form class="bg-white border rounded-xl p-3 flex gap-2 items-end">
    <div><label class="text-xs text-slate-500">As of</label><input type="date" name="date" value="{{ $asOfDate }}" class="border rounded px-3 py-2 text-sm"></div>
    <button class="btn-secondary text-sm">Generate</button>
  </form>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">Code</th><th class="p-3 text-left">Account</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Debit</th><th class="p-3 text-left">Credit</th></tr></thead>
      <tbody>
        @foreach($report['items'] as $i)
        <tr class="border-t"><td class="p-3 font-mono">{{ $i['code'] }}</td><td class="p-3">{{ $i['name'] }}</td><td class="p-3">{{ $i['type'] }}</td><td class="p-3">{{ currency_format($i['debit']) }}</td><td class="p-3">{{ currency_format($i['credit']) }}</td></tr>
        @endforeach
      </tbody>
      <tfoot class="bg-slate-50 font-semibold">
        <tr><td class="p-3" colspan="3">Totals</td><td class="p-3">{{ currency_format($report['total_debit']) }}</td><td class="p-3">{{ currency_format($report['total_credit']) }}</td></tr>
      </tfoot>
    </table>
  </div>
  <div class="p-3 rounded-lg {{ $report['is_balanced'] ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
    {{ $report['is_balanced'] ? 'Trial balance is balanced.' : 'Trial balance mismatch detected.' }}
  </div>
</div>
@endsection


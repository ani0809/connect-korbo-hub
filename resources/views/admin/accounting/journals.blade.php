@extends('admin.layouts.app')
@section('title', 'Journal Entries')
@section('content')
<div class="space-y-4">
  <div class="flex items-center justify-between">
    <form method="get" class="flex flex-wrap gap-2">
      <select name="status" class="border rounded px-3 py-2 text-sm"><option value="">Status</option>@foreach(['draft','posted','voided'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
      <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2 text-sm">
      <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2 text-sm">
      <input type="search" name="search" value="{{ request('search') }}" class="border rounded px-3 py-2 text-sm" placeholder="Search">
      <button class="btn-secondary text-sm">Filter</button>
    </form>
    <a href="{{ route('admin.accounting.journals.create') }}" class="btn-primary">+ New Journal</a>
  </div>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">JE #</th><th class="p-3 text-left">Date</th><th class="p-3 text-left">Description</th><th class="p-3 text-left">Debit</th><th class="p-3 text-left">Credit</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">By</th><th class="p-3 text-left">Actions</th></tr></thead>
      <tbody>
        @foreach($journals as $j)
        @php($debit = (float) $j->lines->sum('debit'))
        @php($credit = (float) $j->lines->sum('credit'))
        <tr class="border-t">
          <td class="p-3 font-mono">{{ $j->journal_number }}</td>
          <td class="p-3">{{ $j->date?->format('Y-m-d') }}</td>
          <td class="p-3">{{ $j->description }}</td>
          <td class="p-3">{{ currency_format($debit) }}</td>
          <td class="p-3">{{ currency_format($credit) }}</td>
          <td class="p-3 uppercase">{{ $j->status }}</td>
          <td class="p-3">{{ $j->creator?->name }}</td>
          <td class="p-3">
            @if($j->status === 'posted')
            <form method="post" action="{{ route('admin.accounting.journals.void', $j->id) }}" class="flex gap-2">
              @csrf
              <input type="text" name="reason" class="border rounded px-2 py-1 text-xs" placeholder="reason">
              <button class="text-red-600 text-xs">Void</button>
            </form>
            @endif
          </td>
        </tr>
        <tr class="bg-slate-50/50">
          <td></td><td colspan="7" class="p-2">
            <div class="text-xs text-slate-600">
              @foreach($j->lines as $ln)
                <div>{{ $ln->account?->code }} - {{ $ln->account?->name }} | Dr {{ currency_format($ln->debit) }} / Cr {{ currency_format($ln->credit) }}</div>
              @endforeach
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  {{ $journals->withQueryString()->links() }}
</div>
@endsection


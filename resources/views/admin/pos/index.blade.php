@extends('admin.layouts.app')
@section('title', 'POS')
@section('content')
<div class="space-y-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h3 class="font-semibold">Point of Sale</h3>
      <p class="text-sm text-slate-600">Open sessions and today’s closed sessions.</p>
    </div>
    <a href="{{ route('admin.pos.open') }}" class="btn-primary">+ Open new session</a>
  </div>

  <div class="bg-white border rounded-xl p-4">
    <h4 class="font-semibold mb-3">Active sessions</h4>
    @if($activeSessions->isEmpty())
      <p class="text-sm text-slate-500">No open POS sessions.</p>
    @else
      <div class="space-y-2">
        @foreach($activeSessions as $s)
          <div class="flex flex-wrap items-center justify-between gap-2 border rounded-lg p-3">
            <div>
              <span class="font-mono font-semibold">{{ $s->terminal_id }}</span>
              <span class="text-slate-500 text-sm">— {{ $s->user?->name }}</span>
              <div class="text-xs text-slate-500">Opened @datetime($s->opened_at) · Sales {{ currency_format((float) $s->total_sales) }}</div>
            </div>
            <a href="{{ route('admin.pos.terminal', $s->id) }}" class="btn-primary text-sm">Open terminal</a>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-left"><tr><th class="p-3">Terminal</th><th class="p-3">Cashier</th><th class="p-3">Status</th><th class="p-3">Opened</th><th class="p-3">Closed</th><th class="p-3">Sales</th><th class="p-3"></th></tr></thead>
      <tbody>
        @foreach($sessions as $s)
          <tr class="border-t">
            <td class="p-3 font-mono">{{ $s->terminal_id }}</td>
            <td class="p-3">{{ $s->user?->name }}</td>
            <td class="p-3"><span class="text-xs px-2 py-1 rounded {{ $s->status === 'open' ? 'bg-green-100 text-green-800' : 'bg-slate-100' }}">{{ ucfirst($s->status) }}</span></td>
            <td class="p-3">@datetime($s->opened_at)</td>
            <td class="p-3">{{ $s->closed_at ? $s->closed_at->format('j/n/Y g:i A') : '—' }}</td>
            <td class="p-3">{{ currency_format((float) $s->total_sales) }}</td>
            <td class="p-3 flex flex-wrap gap-2">
              @if($s->status === 'open')
                <a href="{{ route('admin.pos.terminal', $s->id) }}" class="text-blue-600">Terminal</a>
              @else
                <a href="{{ route('admin.pos.session.report', $s->id) }}" class="text-blue-600">Report</a>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

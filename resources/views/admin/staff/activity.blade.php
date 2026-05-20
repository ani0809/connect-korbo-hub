@extends('admin.layouts.app')
@section('title', 'Staff activity')
@section('content')
<div class="space-y-4">
  <h2 class="text-lg font-semibold">Activity: {{ $staff->name }}</h2>
  <form method="get" class="flex flex-wrap gap-2 items-end bg-white border rounded-xl p-4">
    <div>
      <label class="text-xs text-slate-500">Action</label>
      <select name="action" class="border rounded px-2 py-2 block">
        <option value="">All</option>
        @foreach($actions as $a)
          <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
        @endforeach
      </select>
    </div>
    <div><label class="text-xs text-slate-500">From</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-2 block"></div>
    <div><label class="text-xs text-slate-500">To</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-2 block"></div>
    <button class="btn-primary">Filter</button>
  </form>
  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-xs">
      <thead><tr class="text-left border-b">
        <th class="p-2">Time</th><th class="p-2">Action</th><th class="p-2">Subject</th><th class="p-2">Old</th><th class="p-2">New</th><th class="p-2">IP</th>
      </tr></thead>
      <tbody>
      @foreach($logs as $log)
        <tr class="border-b align-top">
          <td class="p-2 whitespace-nowrap">@datetime($log->created_at)</td>
          <td class="p-2">{{ $log->action }}</td>
          <td class="p-2">{{ $log->subject_type }} #{{ $log->subject_id }}</td>
          <td class="p-2 max-w-xs truncate">{{ json_encode($log->old_values) }}</td>
          <td class="p-2 max-w-xs truncate">{{ json_encode($log->new_values) }}</td>
          <td class="p-2">{{ $log->ip_address }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  {{ $logs->links() }}
</div>
@endsection

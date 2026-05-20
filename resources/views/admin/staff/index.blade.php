@extends('admin.layouts.app')
@section('title', 'Staff')
@section('content')
<div class="space-y-4">
  <div class="flex flex-wrap justify-between gap-4 items-center">
    <div>
      <p class="text-sm text-slate-500">Total: {{ $stats['total'] }} · Active: {{ $stats['active'] }} · Inactive: {{ $stats['inactive'] }}</p>
    </div>
    <a href="{{ route('admin.staff.create') }}" class="btn-primary">+ Add Staff</a>
  </div>
  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left border-b bg-slate-50">
        <th class="p-3">Staff</th><th class="p-3">Email</th><th class="p-3">Status</th><th class="p-3">Permissions</th><th class="p-3">Today</th><th class="p-3">Actions</th>
      </tr></thead>
      <tbody>
      @foreach($staff as $s)
        <tr class="border-b">
          <td class="p-3">
            <div class="font-medium">{{ $s->name }}</div>
            <div class="text-xs text-slate-500">By {{ $s->creator?->name ?? '—' }}</div>
          </td>
          <td class="p-3">{{ $s->email }}</td>
          <td class="p-3">{{ $s->status }}</td>
          <td class="p-3">{{ is_array($s->staff_permissions) ? count($s->staff_permissions) : 0 }}</td>
          <td class="p-3">{{ $s->today_actions }}</td>
          <td class="p-3 whitespace-nowrap">
            <a href="{{ route('admin.staff.edit', $s->id) }}" class="text-blue-600">Edit</a>
            <a href="{{ route('admin.staff.activity', $s->id) }}" class="text-slate-600 ml-2">Log</a>
            <form action="{{ route('admin.staff.toggle-status', $s->id) }}" method="post" class="inline">@csrf
              <button type="submit" class="text-amber-700 ml-2">Toggle</button>
            </form>
            <form action="{{ route('admin.staff.destroy', $s->id) }}" method="post" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
              <button type="submit" class="text-red-600 ml-2">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  {{ $staff->links() }}
</div>
@endsection

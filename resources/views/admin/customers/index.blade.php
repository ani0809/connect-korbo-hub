@extends('admin.layouts.app')
@section('title', 'Customers')
@section('content')
@php use App\Support\StaffPermissions; @endphp
<div class="space-y-4">
  <div class="text-sm text-slate-600">Total {{ $stats['total'] }} · New this month {{ $stats['new_month'] }} · Active {{ $stats['active'] }} · Banned {{ $stats['banned'] }}</div>
  <div class="flex flex-wrap gap-2">
    @if(StaffPermissions::has(auth()->user(), 'create_customers'))
      <a href="{{ route('admin.customers.create') }}" class="btn-primary">+ Add Customer</a>
    @endif
    <a href="{{ route('admin.customers.export', request()->query()) }}" class="btn-secondary">Export CSV</a>
  </div>
  <form method="get" class="grid md:grid-cols-6 gap-2 bg-white border rounded-xl p-4">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Name, email, phone" class="border rounded px-3 py-2 md:col-span-2">
    <select name="status" class="border rounded px-2 py-2"><option value="">Status</option><option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option><option value="banned" {{ request('status')==='banned'?'selected':'' }}>Banned</option></select>
    <select name="verified" class="border rounded px-2 py-2"><option value="">Verified</option><option value="1" {{ request('verified')==='1'?'selected':'' }}>Yes</option><option value="0" {{ request('verified')==='0'?'selected':'' }}>No</option></select>
    <input type="number" step="0.01" name="min_spent" value="{{ request('min_spent') }}" placeholder="Min spent" class="border rounded px-2 py-2">
    <input type="number" step="0.01" name="max_spent" value="{{ request('max_spent') }}" placeholder="Max spent" class="border rounded px-2 py-2">
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-2">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-2">
    <button class="btn-primary md:col-span-6">Apply</button>
  </form>
  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left border-b bg-slate-50"><th class="p-3">Customer</th><th class="p-3">Phone</th><th class="p-3">Orders</th><th class="p-3">Spent</th><th class="p-3">Status</th><th class="p-3">Joined</th><th class="p-3"></th></tr></thead>
      <tbody>
      @foreach($customers as $c)
        <tr class="border-b">
          <td class="p-3"><div class="font-medium">{{ $c->name }}</div><div class="text-xs text-slate-500">{{ $c->email }}</div></td>
          <td class="p-3">{{ $c->phone }}</td>
          <td class="p-3">{{ $c->orders_count }}</td>
          <td class="p-3">{{ currency_format((float) ($c->total_spent ?? 0)) }}</td>
          <td class="p-3">{{ $c->status }}</td>
          <td class="p-3 text-xs">@datetime($c->created_at)</td>
          <td class="p-3 whitespace-nowrap">
            <a href="{{ route('admin.customers.show', $c->id) }}" class="text-blue-600">View</a>
            @if(StaffPermissions::has(auth()->user(), 'edit_customers'))
              <a href="{{ route('admin.customers.edit', $c->id) }}" class="text-slate-700 ml-2">Edit</a>
            @endif
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  {{ $customers->links() }}
</div>
@endsection

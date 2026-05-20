@extends('admin.layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="space-y-4">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">This Month</div><div class="text-xl font-bold">{{ currency_format($stats['this_month']) }}</div></div>
    <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">Last Month</div><div class="text-xl font-bold">{{ currency_format($stats['last_month']) }}</div></div>
    <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">YTD</div><div class="text-xl font-bold">{{ currency_format($stats['ytd']) }}</div></div>
  </div>

  <div class="flex flex-wrap items-center justify-between gap-2">
    <div class="text-sm text-slate-500">Track and manage all expense entries.</div>
    <a href="{{ route('admin.accounting.expenses.create') }}" class="btn-primary">+ Record Expense</a>
  </div>

  <form method="get" class="bg-white border rounded-xl p-3 grid grid-cols-1 md:grid-cols-6 gap-2">
    <select name="category_id" class="border rounded px-3 py-2 text-sm"><option value="">Category</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected((string)request('category_id')===(string)$cat->id)>{{ $cat->name }}</option>@endforeach</select>
    <select name="payment_method" class="border rounded px-3 py-2 text-sm"><option value="">Payment</option>@foreach(['cash','bank','mobile','card'] as $m)<option value="{{ $m }}" @selected(request('payment_method')===$m)>{{ ucfirst($m) }}</option>@endforeach</select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2 text-sm">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2 text-sm">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search" class="border rounded px-3 py-2 text-sm">
    <button class="btn-secondary text-sm">Filter</button>
  </form>

  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">Date</th><th class="p-3 text-left">#</th><th class="p-3 text-left">Category</th><th class="p-3 text-left">Description</th><th class="p-3 text-left">Payment</th><th class="p-3 text-left">Amount</th><th class="p-3 text-left">Attachment</th><th class="p-3 text-left">Actions</th></tr></thead>
      <tbody>
      @forelse($expenses as $exp)
        <tr class="border-t">
          <td class="p-3">{{ $exp->date?->format('Y-m-d') }}</td>
          <td class="p-3 font-mono">{{ $exp->expense_number }}</td>
          <td class="p-3"><span class="px-2 py-1 rounded text-xs text-white" style="background: {{ $exp->category?->color ?? '#64748b' }}">{{ $exp->category?->name }}</span></td>
          <td class="p-3">{{ $exp->description }}</td>
          <td class="p-3 uppercase">{{ $exp->payment_method }}</td>
          <td class="p-3 font-semibold">{{ currency_format($exp->total) }}</td>
          <td class="p-3">@if($exp->attachment)<a class="text-blue-600" target="_blank" href="{{ asset('storage/'.$exp->attachment) }}">View</a>@else — @endif</td>
          <td class="p-3">
            <a href="{{ route('admin.accounting.expenses.edit', $exp->id) }}" class="text-blue-600 text-xs">Edit</a>
            <form method="post" action="{{ route('admin.accounting.expenses.destroy', $exp->id) }}" class="inline" onsubmit="return confirm('Delete expense?')">
              @csrf @method('DELETE')
              <button class="text-red-600 text-xs ml-2">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td class="p-6 text-center text-slate-500" colspan="8">No expenses found.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $expenses->withQueryString()->links() }}
</div>
@endsection


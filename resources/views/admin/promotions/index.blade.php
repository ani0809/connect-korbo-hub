@extends('admin.layouts.app')
@section('title','Promotions')
@section('content')
<div class="flex items-center justify-between mb-4">
  <div class="text-sm text-slate-600">
    Total Promotions: {{ $stats['total'] }} | Active: {{ $stats['active'] }} | Total Discount Given: {{ currency_format($stats['total_discount_given']) }}
  </div>
  <a href="{{ route('admin.promotions.create') }}" class="btn-primary">+ Create Promotion</a>
</div>

<form method="get" class="flex flex-wrap gap-2 mb-4">
  <input type="search" name="search" value="{{ request('search') }}" placeholder="Search" class="border rounded px-3 py-2 text-sm">
  <select name="type" class="border rounded px-3 py-2 text-sm">
    <option value="">All Types</option>
    @foreach(['buy_x_get_y'=>'Buy X Get Y','quantity_discount'=>'Qty Discount','bundle'=>'Bundle','free_shipping'=>'Free Shipping','flash_sale'=>'Flash Sale','combo_discount'=>'Combo'] as $k => $v)
      <option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>
    @endforeach
  </select>
  <select name="status" class="border rounded px-3 py-2 text-sm">
    <option value="">All Status</option>
    <option value="active" @selected(request('status')==='active')>Active</option>
    <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
    <option value="expired" @selected(request('status')==='expired')>Expired</option>
  </select>
  <button class="btn-secondary text-sm">Filter</button>
</form>

<div class="bg-white border rounded-xl overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50">
      <tr>
        <th class="p-3 text-left">Name</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Applied</th><th class="p-3 text-left">Validity</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($promotions as $promotion)
      <tr class="border-t">
        <td class="p-3">
          @if($promotion->badge_text)<span class="inline-block text-xs px-2 py-1 rounded text-white mr-1" style="background:{{ $promotion->badge_color }}">{{ $promotion->badge_text }}</span>@endif
          {{ $promotion->name }}
        </td>
        <td class="p-3">{{ str_replace('_',' ', $promotion->type) }}</td>
        <td class="p-3">{{ $promotion->usages_count }} times</td>
        <td class="p-3">{{ $promotion->starts_at?->format('d M') ?? 'Now' }} -> {{ $promotion->expires_at?->format('d M') ?? 'No expiry' }}</td>
        <td class="p-3">{{ $promotion->isCurrentlyActive() ? 'Active' : 'Inactive' }}</td>
        <td class="p-3 flex gap-2">
          <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="text-blue-600">Edit</a>
          <form method="post" action="{{ route('admin.promotions.duplicate', $promotion->id) }}">@csrf<button class="text-amber-700">Duplicate</button></form>
          <form method="post" action="{{ route('admin.promotions.destroy', $promotion->id) }}" onsubmit="return confirm('Delete this promotion?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $promotions->withQueryString()->links() }}</div>
@endsection

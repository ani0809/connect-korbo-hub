@extends('admin.layouts.app')
@section('title','Orders')
@section('content')
<div class="space-y-4">
  <div class="card">
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 border-b border-[hsl(var(--border))]">
      <div>
        <h3>Orders</h3>
        <p>Track order lifecycle, payment, and fulfillment progress.</p>
      </div>
    </div>
    <form class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5 p-4 md:p-5 items-end" method="GET">
      <input name="search" value="{{ request('search') }}" class="col-span-2 form-input px-3 py-2.5" placeholder="Search order number/customer">
      <select name="status" class="form-input px-2 py-2.5"><option value="">All Status</option>@foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
      <select name="payment_status" class="form-input px-2 py-2.5"><option value="">Payment</option>@foreach(['pending','paid','failed'] as $s)<option value="{{ $s }}" @selected(request('payment_status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
      <select name="seller" class="form-input px-2 py-2.5"><option value="">Seller</option>@foreach($sellers as $seller)<option value="{{ $seller->id }}" @selected((int)request('seller')===$seller->id)>{{ $seller->shop_name }}</option>@endforeach</select>
      <button class="btn-primary h-11">Apply</button>
    </form>
  </div>

  <div class="card overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-[hsl(var(--muted))]">
        <tr class="border-b border-[hsl(var(--border))] text-left">
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">#</th>
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">Order No</th>
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">Customer</th>
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">Items</th>
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">Total</th>
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">Payment</th>
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">Status</th>
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">Date</th>
          <th class="px-3 py-2.5 text-[hsl(var(--muted-foreground))] font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody>
      @foreach($orders as $order)
        <tr class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--muted)/0.45)] transition-colors">
          <td class="px-3 py-2.5">{{ $loop->iteration }}</td>
          <td class="px-3 py-2.5">@if(!empty($order->is_pos_order))<span class="text-xs font-bold mr-1 px-1.5 py-0.5 rounded bg-[hsl(var(--muted))] text-[hsl(var(--foreground))]">POS</span>@endif<strong>{{ $order->order_number }}</strong></td>
          <td class="px-3 py-2.5">{{ $order->user?->name ?: $order->guest_name }}</td>
          <td class="px-3 py-2.5">{{ $order->items->count() }}</td>
          <td class="px-3 py-2.5 font-medium">{{ currency_format((float)$order->total) }}</td>
          <td class="px-3 py-2.5"><span class="status-badge {{ $order->payment_status === 'paid' ? 'status-delivered' : ($order->payment_status === 'failed' ? 'status-failed' : 'status-pending') }}">{{ strtolower($order->payment_status) }}</span></td>
          <td class="px-3 py-2.5"><span class="status-badge {{ 'status-' . $order->order_status }}">{{ strtolower($order->order_status) }}</span></td>
          <td class="px-3 py-2.5">@datetime($order->created_at)</td>
          <td class="px-3 py-2.5"><a class="link-primary font-medium" href="{{ route('admin.orders.show',$order->id) }}">View</a> <a class="text-[hsl(var(--muted-foreground))] font-medium" href="{{ route('admin.orders.invoice',$order->id) }}">Invoice</a></td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <div class="pt-1">{{ $orders->links() }}</div>
</div>
@endsection

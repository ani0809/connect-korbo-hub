@extends('admin.layouts.app')
@section('title','Order Details')
@section('content')
@if(!empty($order->is_pos_order))
<div class="mb-4 px-3 py-2 rounded-lg bg-indigo-50 border border-indigo-200 text-sm text-indigo-900 font-medium">POS sale @if($order->cashier) · Cashier: {{ $order->cashier->name }} @endif @if($order->posSession) · Terminal: {{ $order->posSession->terminal_id }} @endif</div>
@endif
<div class="grid grid-cols-12 gap-4">
  <div class="col-span-8 space-y-4">
    <div class="bg-white border rounded-xl p-4 overflow-x-auto">
      <h3 class="font-semibold mb-3">Order Items</h3>
      <table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th>Status</th></tr></thead><tbody>
      @foreach($order->items as $item)
        <tr class="border-b"><td class="py-2"><div class="font-medium">{{ $item->product_name }}</div><div class="text-xs text-gray-500">Variant: {{ collect($item->variant_info ?? [])->map(fn($v,$k)=>$k.': '.$v)->join(', ') ?: 'N/A' }}</div><div class="text-xs">Seller: {{ $item->seller?->shop_name ?: 'Admin' }}</div></td><td>{{ $item->quantity }}</td><td>{{ currency_format((float)$item->unit_price) }}</td><td>{{ currency_format((float)$item->subtotal) }}</td><td><form method="POST" action="{{ route('admin.orders.item-status') }}">@csrf<input type="hidden" name="item_id" value="{{ $item->id }}"><select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1 text-xs">@foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)<option value="{{ $s }}" @selected($item->item_status===$s)>{{ ucfirst($s) }}</option>@endforeach</select></form></td></tr>
      @endforeach
      </tbody></table>
    </div>

    <div class="bg-white border rounded-xl p-4">
      <h3 class="font-semibold mb-3">Order Timeline</h3>
      <div class="space-y-3">@foreach($order->statusHistory as $row)<div class="border-l-2 pl-3"><div class="font-medium">{{ ucfirst($row->status) }} � @datetime($row->created_at)</div><div class="text-sm text-gray-600">{{ $row->comment }}</div></div>@endforeach</div>
    </div>
  </div>

  <div class="col-span-4 space-y-4">
    <div class="bg-white border rounded-xl p-4"><h3 class="font-semibold">Customer Info</h3><div class="text-sm mt-2">{{ $order->user?->name ?: $order->guest_name }}</div><div class="text-sm">{{ $order->user?->email ?: $order->guest_email }}</div><div class="text-sm">{{ $order->shipping_phone }}</div></div>
    <div class="bg-white border rounded-xl p-4"><h3 class="font-semibold">Shipping Address</h3><div class="text-sm mt-2">{{ $order->shipping_name }}</div><div class="text-sm">{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }}, {{ $order->shipping_country }} {{ $order->shipping_postal_code }}</div></div>
    <div class="bg-white border rounded-xl p-4"><h3 class="font-semibold">Payment Info</h3><div class="text-sm mt-2">Method: {{ strtoupper($order->payment_method) }}</div><div class="text-sm">Status: {{ ucfirst($order->payment_status) }}</div><div class="text-sm">Transaction: {{ $order->payment_reference ?: 'N/A' }}</div></div>
    <div class="bg-white border rounded-xl p-4"><h3 class="font-semibold">Order Summary</h3><div class="text-sm mt-2 space-y-1"><div class="flex justify-between"><span>Subtotal</span><strong>{{ currency_format((float)$order->subtotal) }}</strong></div><div class="flex justify-between"><span>Shipping</span><strong>{{ currency_format((float)$order->shipping_cost) }}</strong></div><div class="flex justify-between"><span>Tax</span><strong>{{ currency_format((float)$order->tax_amount) }}</strong></div><div class="flex justify-between"><span>Discount</span><strong>-{{ currency_format((float)$order->coupon_discount) }}</strong></div><hr><div class="flex justify-between"><span>Total</span><strong>{{ currency_format((float)$order->total) }}</strong></div></div></div>
    <div class="bg-white border rounded-xl p-4"><h3 class="font-semibold">Actions</h3><form method="POST" action="{{ route('admin.orders.status') }}" class="space-y-2 mt-2">@csrf<input type="hidden" name="order_id" value="{{ $order->id }}"><select name="status" class="w-full border rounded px-2 py-2">@foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)<option value="{{ $s }}" @selected($order->order_status===$s)>{{ ucfirst($s) }}</option>@endforeach</select><textarea name="comment" class="w-full border rounded p-2" rows="2" placeholder="Comment"></textarea><button class="w-full btn-primary">Update</button></form><a href="{{ route('admin.orders.invoice',$order->id) }}" class="mt-2 block btn-secondary text-center">Print Invoice</a></div>
  </div>
</div>
@endsection

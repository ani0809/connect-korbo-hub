@extends('frontend.layouts.app')
@section('title', 'Order Details')
@section('content')
<div class="container py-8">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-heading font-semibold">Order {{ $order->order_number }}</h2>
    <span class="badge status-badge">{{ strtoupper($order->order_status) }}</span>
  </div>
  <div class="card mb-4 p-5">
    <div>
      <div><strong>Payment:</strong> {{ strtoupper($order->payment_status) }}</div>
      <div><strong>Shipping:</strong> {{ $order->shipping_name }}, {{ $order->shipping_address }}, {{ $order->shipping_city }}</div>
      <div><strong>Phone:</strong> {{ $order->shipping_phone }}</div>
    </div>
  </div>
  <div class="card p-0 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="table w-full mb-0">
        <thead><tr><th>Product</th><th>Qty</th><th class="text-end">Total</th></tr></thead>
        <tbody>
          @foreach($order->items as $item)
            <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td class="text-end">{{ currency_format($item->subtotal) }}</td></tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr><th colspan="2">Total</th><th class="text-end">{{ currency_format($order->total) }}</th></tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endsection


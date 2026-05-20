@extends('admin.layouts.app')
@section('title', 'POS Session '.$session->terminal_id)
@section('content')
@php
  $variance = ($session->closing_balance !== null && $session->expected_balance !== null)
    ? (float) $session->closing_balance - (float) $session->expected_balance
    : null;
  $warn = (float) setting('pos_variance_warning_amount', 50);
@endphp

<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
  <div>
    <h1 class="text-xl font-bold">POS session report</h1>
    <p class="text-sm text-slate-600">
      {{ $session->terminal_id }} · {{ $session->user?->name }}
      · {{ $session->opened_at?->format('d M Y, H:i') }}
      → {{ $session->closed_at?->format('H:i') ?? 'Open' }}
    </p>
  </div>
  <button type="button" class="btn-secondary" onclick="window.print()">Print report</button>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-white border rounded-xl p-4">
    <div class="text-2xl font-bold">{{ currency_format((float) $session->total_sales) }}</div>
    <div class="text-xs text-slate-500">Total sales</div>
  </div>
  <div class="bg-white border rounded-xl p-4">
    <div class="text-2xl font-bold">{{ $session->total_orders }}</div>
    <div class="text-xs text-slate-500">Transactions</div>
  </div>
  <div class="bg-white border rounded-xl p-4">
    <div class="text-2xl font-bold">{{ currency_format((float) $session->discount_given) }}</div>
    <div class="text-xs text-slate-500">Discounts</div>
  </div>
  <div class="bg-white border rounded-xl p-4">
    <div class="text-2xl font-bold">{{ currency_format((float) $session->total_returns) }}</div>
    <div class="text-xs text-slate-500">Returns</div>
  </div>
</div>

<div class="bg-white border rounded-xl p-4 mb-6">
  <h3 class="font-semibold mb-4">Cash reconciliation</h3>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div>
      <div class="text-xs text-slate-500 mb-1">Opening</div>
      <div class="text-xl font-bold">{{ currency_format((float) $session->opening_balance) }}</div>
    </div>
    <div>
      <div class="text-xs text-slate-500 mb-1">Expected closing</div>
      <div class="text-xl font-bold">{{ currency_format((float) ($session->expected_balance ?? 0)) }}</div>
    </div>
    <div>
      <div class="text-xs text-slate-500 mb-1">Actual closing</div>
      <div class="text-xl font-bold @if($variance !== null && abs($variance) >= $warn) text-red-600 @elseif($variance !== null) text-green-600 @endif">
        {{ currency_format((float) ($session->closing_balance ?? 0)) }}
      </div>
    </div>
  </div>
  @if($variance !== null)
  <div class="mt-4 p-3 rounded-lg @if(abs($variance) < 1) bg-green-50 text-green-800 @elseif(abs($variance) >= $warn) bg-red-50 text-red-800 @else bg-amber-50 text-amber-900 @endif text-sm font-medium">
    Variance: {{ $variance >= 0 ? '+' : '' }}{{ currency_format($variance) }}
    @if(abs($variance) < 1) — Balanced
    @elseif(abs($variance) >= $warn) — Review required (≥ {{ currency_format($warn) }})
    @else — Minor difference
    @endif
  </div>
  @endif
  @if($session->notes)
    <p class="text-sm text-slate-600 mt-3"><strong>Notes:</strong> {{ $session->notes }}</p>
  @endif
</div>

<div class="bg-white border rounded-xl p-4 mb-6">
  <h3 class="font-semibold mb-4">Payment breakdown</h3>
  <div class="grid grid-cols-3 gap-4 text-center">
    <div class="bg-slate-50 rounded-lg p-4">
      <div class="text-lg font-bold">{{ currency_format((float) $session->cash_sales) }}</div>
      <div class="text-xs text-slate-500">Cash</div>
    </div>
    <div class="bg-slate-50 rounded-lg p-4">
      <div class="text-lg font-bold">{{ currency_format((float) $session->card_sales) }}</div>
      <div class="text-xs text-slate-500">Card</div>
    </div>
    <div class="bg-slate-50 rounded-lg p-4">
      <div class="text-lg font-bold">{{ currency_format((float) $session->mobile_sales) }}</div>
      <div class="text-xs text-slate-500">Mobile</div>
    </div>
  </div>
</div>

<div class="bg-white border rounded-xl overflow-x-auto">
  <h3 class="font-semibold p-4 border-b">Orders in this session</h3>
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-left"><tr><th class="p-3">Order</th><th class="p-3">Items</th><th class="p-3">Customer</th><th class="p-3">Payment</th><th class="p-3">Discount</th><th class="p-3">Total</th><th class="p-3">Time</th></tr></thead>
    <tbody>
      @forelse($session->orders as $order)
        <tr class="border-t">
          <td class="p-3"><a href="{{ route('admin.orders.show', $order->id) }}" class="text-blue-600 font-medium">{{ $order->order_number }}</a></td>
          <td class="p-3">{{ $order->items->sum('quantity') }}</td>
          <td class="p-3">{{ $order->user?->name ?? $order->guest_name ?? 'Walk-in' }}</td>
          <td class="p-3 uppercase">{{ $order->payment_method }}</td>
          <td class="p-3">@if((float)$order->coupon_discount > 0)-{{ currency_format((float)$order->coupon_discount) }}@else — @endif</td>
          <td class="p-3 font-semibold">{{ currency_format((float)$order->total) }}</td>
          <td class="p-3">{{ $order->created_at?->format('H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="p-6 text-center text-slate-500">No orders in this session.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="bg-white border rounded-xl p-4 mt-6">
  <h3 class="font-semibold mb-3">Drawer movements (cash in/out)</h3>
  <table class="w-full text-sm">
    <thead><tr class="text-left border-b"><th class="py-2">Time</th><th class="py-2">Type</th><th class="py-2">Amount</th><th class="py-2">Note</th></tr></thead>
    <tbody>
      @foreach($session->transactions->whereIn('type', ['cash_in','cash_out']) as $tx)
        <tr class="border-t">
          <td class="py-2">{{ $tx->created_at?->format('H:i:s') }}</td>
          <td class="py-2">{{ str_replace('_',' ', $tx->type) }}</td>
          <td class="py-2">{{ currency_format((float)$tx->amount) }}</td>
          <td class="py-2">{{ $tx->note }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection

@extends('admin.layouts.app')
@section('title','Coupon '.$coupon->code)
@section('content')
<div class="mb-4">
  <a href="{{ route('admin.coupons.index') }}" class="text-sm text-blue-600">← Back</a>
  <h2 class="text-xl font-semibold mt-2">{{ $coupon->code }} — {{ $coupon->type === 'percent' ? $coupon->amount.'%' : currency_format((float)$coupon->amount) }}</h2>
</div>
<div class="grid md:grid-cols-3 gap-3 mb-6">
  <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">Uses</div><div class="text-xl font-bold">{{ $stats['total_uses'] }} @if(is_numeric($stats['remaining'])) / {{ $coupon->usage_limit }} @endif</div></div>
  <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">Discount given</div><div class="text-xl font-bold">{{ currency_format($stats['total_discount_given']) }}</div></div>
  <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">Paid revenue</div><div class="text-xl font-bold">{{ currency_format($stats['total_revenue']) }}</div></div>
</div>
<div class="bg-white border rounded-xl overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="bg-slate-50 text-left"><th class="p-3">Order</th><th class="p-3">Customer</th><th class="p-3">Total</th><th class="p-3">Discount</th><th class="p-3">Date</th></tr></thead>
    <tbody>
      @foreach($orders as $o)
        <tr class="border-t">
          <td class="p-3 font-mono">{{ $o->order_number }}</td>
          <td class="p-3">{{ $o->user?->email ?? '—' }}</td>
          <td class="p-3">{{ currency_format((float)$o->total) }}</td>
          <td class="p-3">{{ currency_format((float)$o->coupon_discount) }}</td>
          <td class="p-3">@datetime($o->created_at)</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $orders->links() }}
@endsection

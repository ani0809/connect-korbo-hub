<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoice #{{ $order->order_number }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1e293b; }
    .wrap { max-width: 780px; margin: 0 auto; padding: 24px; }
    .head { display: flex; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 14px; margin-bottom: 16px; }
    .title { font-size: 28px; font-weight: 800; color: #2563eb; }
    .muted { color: #64748b; }
    .meta td { padding: 2px 0; }
    .grid { display: flex; justify-content: space-between; margin-bottom: 12px; }
    .col { width: 48%; }
    .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .table th { background: #2563eb; color: #fff; font-size: 10px; text-transform: uppercase; padding: 8px; text-align: left; }
    .table td { border-bottom: 1px solid #e2e8f0; padding: 8px; }
    .table td.r, .table th.r { text-align: right; }
    .totals { width: 280px; margin-left: auto; margin-top: 12px; }
    .totals .row { display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding: 6px 0; }
    .grand { display: flex; justify-content: space-between; background: #2563eb; color: #fff; padding: 10px 12px; border-radius: 6px; margin-top: 8px; font-weight: 700; }
    .footer { border-top: 1px solid #e2e8f0; margin-top: 18px; padding-top: 8px; font-size: 11px; color: #64748b; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <div>
      <div style="font-size:20px;font-weight:700">{{ setting('site_name') }}</div>
      <div class="muted">{{ setting('address') }}</div>
      <div class="muted">{{ setting('contact_phone') }} | {{ setting('contact_email') }}</div>
    </div>
    <div style="text-align:right">
      <div class="title">INVOICE</div>
      <table class="meta">
        <tr><td class="muted">Invoice #</td><td><strong>{{ $order->order_number }}</strong></td></tr>
        <tr><td class="muted">Date</td><td>{{ $order->created_at?->format('d M Y') }}</td></tr>
        <tr><td class="muted">Payment</td><td>{{ strtoupper($order->payment_method) }}</td></tr>
      </table>
    </div>
  </div>

  <div class="grid">
    <div class="col">
      <div class="muted" style="font-size:10px;text-transform:uppercase">Bill To</div>
      <div style="font-weight:700">{{ $order->shipping_name ?: ($order->user?->name ?? 'Customer') }}</div>
      <div class="muted">{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_country }}</div>
      <div class="muted">{{ $order->shipping_phone }}</div>
    </div>
    <div class="col" style="text-align:right">
      <div class="muted">Status: <strong>{{ strtoupper($order->payment_status) }}</strong></div>
      <div class="muted">Order status: <strong>{{ strtoupper($order->order_status) }}</strong></div>
    </div>
  </div>

  <table class="table">
    <thead><tr><th>#</th><th>Product</th><th class="r">Qty</th><th class="r">Unit</th><th class="r">Total</th></tr></thead>
    <tbody>
      @foreach($order->items as $idx => $item)
      <tr>
        <td>{{ $idx + 1 }}</td>
        <td>{{ $item->product_name }}</td>
        <td class="r">{{ $item->quantity }}</td>
        <td class="r">{{ currency_format($item->unit_price) }}</td>
        <td class="r">{{ currency_format($item->subtotal) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="totals">
    <div class="row"><span>Subtotal</span><span>{{ currency_format($order->subtotal) }}</span></div>
    <div class="row"><span>Shipping</span><span>{{ currency_format($order->shipping_cost) }}</span></div>
    @if((float)$order->coupon_discount > 0)<div class="row"><span>Discount</span><span>-{{ currency_format($order->coupon_discount) }}</span></div>@endif
    @if((float)$order->tax_amount > 0)<div class="row"><span>{{ setting('tax_name', 'VAT') }}</span><span>{{ currency_format($order->tax_amount) }}</span></div>@endif
    <div class="grand"><span>Total Due</span><span>{{ currency_format($order->total) }}</span></div>
  </div>

  <div class="footer">
    {{ setting('invoice_footer', 'Thank you for your business.') }}
  </div>
</div>
</body>
</html>

<!doctype html>
<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:6px}.right{text-align:right}.muted{color:#666}.watermark{position:fixed;top:40%;left:25%;font-size:72px;color:rgba(16,185,129,.14);transform:rotate(-30deg)}</style></head>
<body>
@php
    $pointsEarned = (int) \Illuminate\Support\Facades\DB::table('club_point_transactions')->where('order_id', $order->id)->where('type', 'earned')->sum('points');
    $preWalletTotal = max(0, (float) $order->subtotal - (float) ($order->coupon_discount ?? 0) - (float) ($order->points_discount ?? 0) + (float) $order->shipping_cost + (float) $order->tax_amount);
@endphp
@if($order->payment_status==='paid')<div class="watermark">PAID</div>@endif
<table style="border:none"><tr><td style="border:none"><h2>INVOICE</h2><div>Order: {{ $order->order_number }}</div><div>Issue Date: {{ $order->created_at?->format('Y-m-d') }}</div></td><td style="border:none" class="right"><div><strong>{{ setting('site_name','Cibato Commerce') }}</strong></div><div class="muted">{{ setting('site_email','') }}</div><div class="muted">{{ setting('site_phone','') }}</div></td></tr></table>
<table style="border:none;margin-top:10px"><tr><td style="border:none"><strong>Bill To</strong><div>{{ $order->user?->name ?: $order->guest_name }}</div><div>{{ $order->user?->email ?: $order->guest_email }}</div></td><td style="border:none"><strong>Ship To</strong><div>{{ $order->shipping_name }}</div><div>{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_country }}</div></td></tr></table>

<table style="margin-top:10px"><thead><tr><th>#</th><th>Product</th><th>Variant</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->product_name }}</td><td>{{ collect($item->variant_info ?? [])->map(fn($v,$k)=>$k.': '.$v)->join(', ') ?: '—' }}</td><td>{{ $item->quantity }}</td><td class="right">{{ currency_format((float)$item->unit_price) }}</td><td class="right">{{ currency_format((float)$item->subtotal) }}</td></tr>@endforeach</tbody></table>

<table style="width:42%;margin-left:auto;margin-top:10px">
<tr><td>Subtotal</td><td class="right">{{ currency_format((float)$order->subtotal) }}</td></tr>
<tr><td>Shipping</td><td class="right">{{ currency_format((float)$order->shipping_cost) }}</td></tr>
<tr><td>Tax</td><td class="right">{{ currency_format((float)$order->tax_amount) }}</td></tr>
@if((float)($order->coupon_discount ?? 0) > 0)<tr><td>Coupon ({{ $order->coupon_code ?? '—' }})</td><td class="right">-{{ currency_format((float)$order->coupon_discount) }}</td></tr>@endif
@if((float)($order->points_discount ?? 0) > 0)<tr><td>Reward points redeemed</td><td class="right">-{{ currency_format((float)$order->points_discount) }}</td></tr>@endif
<tr><td><strong>Order total (before wallet)</strong></td><td class="right"><strong>{{ currency_format($preWalletTotal) }}</strong></td></tr>
@if((float)($order->wallet_amount_used ?? 0) > 0)<tr><td>Wallet applied</td><td class="right">-{{ currency_format((float)$order->wallet_amount_used) }}</td></tr>@endif
<tr><td><strong>Amount due / charged</strong></td><td class="right"><strong>{{ currency_format((float)$order->total) }}</strong></td></tr>
</table>

<table style="margin-top:14px;border:none;width:100%"><tr><td style="border:none" class="muted">
<strong>Payment</strong><br>
Method: {{ strtoupper((string)($order->payment_method ?? '—')) }}<br>
Status: {{ ucfirst((string)($order->payment_status ?? '—')) }}
@if((float)($order->wallet_amount_used ?? 0) > 0)<br>Wallet used: {{ currency_format((float)$order->wallet_amount_used) }}@endif
</td><td style="border:none" class="right muted">
@if($pointsEarned > 0)<strong>Points earned:</strong> {{ number_format($pointsEarned) }} pts<br>@endif
@if((int)($order->points_used ?? 0) > 0)<span>Points redeemed: {{ number_format((int)$order->points_used) }} pts</span>@endif
</td></tr></table>

<p style="margin-top:20px" class="muted">Thank you for your order. For returns and support contact our support team.</p>
</body></html>

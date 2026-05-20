<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoice {{ $order->order_number }}</title>
  <style>
    body { font-family: DejaVu Sans, monospace; font-size: 11px; color: #000; }
    .w { width: 280px; margin: 0 auto; }
    .c { text-align: center; }
    .line { border-top: 1px dashed #000; margin: 6px 0; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 2px 0; vertical-align: top; }
    .r { text-align: right; }
  </style>
</head>
<body>
<div class="w">
  <div class="c">
    <strong>{{ setting('site_name') }}</strong><br>
    {{ setting('contact_phone') }}<br>
    {{ setting('address') }}
  </div>
  <div class="line"></div>
  Invoice: {{ $order->order_number }}<br>
  Date: @datetime($order->created_at)<br>
  Customer: {{ $order->shipping_name ?: ($order->user?->name ?? 'Guest') }}<br>
  <div class="line"></div>
  <table>
    @foreach($order->items as $item)
    <tr><td colspan="2">{{ $item->product_name }}</td></tr>
    <tr><td>{{ $item->quantity }} x {{ number_format((float)$item->unit_price, 2) }}</td><td class="r">{{ number_format((float)$item->subtotal, 2) }}</td></tr>
    @endforeach
  </table>
  <div class="line"></div>
  <table>
    <tr><td>Subtotal</td><td class="r">{{ number_format((float)$order->subtotal, 2) }}</td></tr>
    <tr><td>Shipping</td><td class="r">{{ number_format((float)$order->shipping_cost, 2) }}</td></tr>
    <tr><td>Tax</td><td class="r">{{ number_format((float)$order->tax_amount, 2) }}</td></tr>
    <tr><td><strong>Total</strong></td><td class="r"><strong>{{ number_format((float)$order->total, 2) }}</strong></td></tr>
  </table>
  <div class="line"></div>
  <div class="c">Thank you</div>
</div>
</body>
</html>


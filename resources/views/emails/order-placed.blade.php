@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;color:#111827;margin:0 0 16px;">?? Order Confirmed!</h1>
<p style="margin:0 0 12px;">Hi {{ $order->shipping_name }},</p>
<p style="margin:0 0 16px;">Thank you for your order! Your order has been placed successfully.</p>
<div style="background:#f0f9ff;border-left:4px solid {{ setting('primary_color','#2563eb') }};padding:14px;border-radius:0 8px 8px 0;margin:16px 0;">
  <strong>Order Number:</strong> {{ $order->order_number }}<br>
  <strong>Order Date:</strong> @datetime($order->created_at)<br>
  <strong>Payment:</strong> {{ strtoupper($order->payment_status) }}
</div>
<p style="margin:18px 0 0;"><a href="{{ url('/account/orders/'.$order->order_number) }}" style="display:inline-block;padding:12px 28px;background:{{ setting('primary_color','#2563eb') }};color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Track Your Order ?</a></p>
@endsection

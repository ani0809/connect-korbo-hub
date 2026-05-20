@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;margin:0 0 12px;">New Order Received</h1>
<p>You have a new order with items from your shop.</p>
<p><strong>Order:</strong> {{ $order->order_number }}</p>
<p><strong>Your Earning:</strong> {{ currency_format((float) $earning) }}</p>
<p style="margin-top:16px;"><a href="{{ url('/seller/orders/'.$order->order_number) }}" style="display:inline-block;padding:12px 24px;background:{{ setting('primary_color','#2563eb') }};color:#fff;text-decoration:none;border-radius:8px;">View in Seller Panel</a></p>
@endsection

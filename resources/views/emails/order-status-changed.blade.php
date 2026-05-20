@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;margin:0 0 14px;">Order Status Updated</h1>
<p>Your order <strong>{{ $order->order_number }}</strong> is now <strong>{{ ucfirst($order->order_status) }}</strong>.</p>
<p style="margin-top:16px;"><a href="{{ url('/account/orders/'.$order->order_number) }}" style="display:inline-block;padding:12px 24px;background:{{ setting('primary_color','#2563eb') }};color:#fff;text-decoration:none;border-radius:8px;">View Order</a></p>
@endsection

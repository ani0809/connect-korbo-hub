@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;margin:0 0 12px;">Seller Account Approved</h1>
<p>Welcome {{ $seller->shop_name ?? '' }}, your seller account is now approved.</p>
<p>Shop URL: {{ url('/shop/'.($seller->shop_slug ?? '')) }}</p>
<ol><li>Login to seller panel</li><li>Add your first product</li><li>Set your shop profile</li></ol>
<p><a href="{{ url('/seller/dashboard') }}" style="display:inline-block;padding:12px 24px;background:{{ setting('primary_color','#2563eb') }};color:#fff;text-decoration:none;border-radius:8px;">Go to Seller Panel</a></p>
@endsection

@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;margin:0 0 12px;">Welcome to {{ setting('site_name') }}</h1>
<p>Hi {{ $name ?? 'there' }}, thanks for joining us.</p>
<p><a href="{{ url('/shop') }}" style="display:inline-block;padding:12px 24px;background:{{ setting('primary_color','#2563eb') }};color:#fff;text-decoration:none;border-radius:8px;">Start Shopping</a></p>
@endsection

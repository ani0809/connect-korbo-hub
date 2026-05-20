@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;margin:0 0 12px;">Password Reset Request</h1>
<p>Click the button below to reset your password. This link expires in 60 minutes.</p>
<p><a href="{{ $resetUrl ?? '#' }}" style="display:inline-block;padding:12px 24px;background:{{ setting('primary_color','#2563eb') }};color:#fff;text-decoration:none;border-radius:8px;">Reset Password</a></p>
<p style="font-size:13px;color:#6b7280;">If you did not request this, ignore this email.</p>
@endsection

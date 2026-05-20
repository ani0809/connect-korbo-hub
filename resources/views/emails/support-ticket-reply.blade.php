@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;margin:0 0 12px;">Support Ticket Reply</h1>
<p>Ticket <strong>{{ $ticketNumber ?? '' }}</strong> has a new reply.</p>
<p>{{ $preview ?? '' }}</p>
<p><a href="{{ $url ?? url('/account/support') }}" style="display:inline-block;padding:12px 24px;background:{{ setting('primary_color','#2563eb') }};color:#fff;text-decoration:none;border-radius:8px;">View Full Conversation</a></p>
@endsection

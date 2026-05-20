@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;margin:0 0 12px;">{{ $subject }}</h1>
<div>{!! $content !!}</div>
@endsection
@section('unsubscribe')
<p style="margin-top:10px;"><a href="{{ $unsubscribeUrl }}" style="color:#94a3b8;">Unsubscribe</a></p>
@endsection

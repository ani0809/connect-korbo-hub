@extends('emails.layouts.master')
@section('content')
<h1 style="font-size:24px;margin:0 0 12px;">{{ $subject ?? setting('site_name') }}</h1>
<div>{!! $body ?? '' !!}</div>
@endsection

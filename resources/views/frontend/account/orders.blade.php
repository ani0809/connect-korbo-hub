@extends('frontend.account.layouts.app')
@section('account-content')
<div class="empty-state">
  <div class="text-lg font-semibold text-slate-700">Orders Page Moved</div>
  <p class="text-sm mt-1">Use the current orders module from your account menu.</p>
  <a href="{{ route('account.orders') }}" class="btn-primary mt-3 inline-block">Open Orders</a>
</div>
@endsection

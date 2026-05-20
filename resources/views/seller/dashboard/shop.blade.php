@extends('frontend.layouts.app')
@section('title','Seller Shop')
@section('content')
<div class="container py-16">
  <div class="max-w-2xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm p-8 text-center">
    <h1 class="text-2xl font-semibold">Seller Shop Module</h1>
    <p class="text-slate-600 mt-2">This legacy seller shop view is replaced by the active seller routes.</p>
    <a href="{{ route('seller.dashboard') }}" class="btn-primary mt-5 inline-block">Go to Seller Dashboard</a>
  </div>
</div>
@endsection

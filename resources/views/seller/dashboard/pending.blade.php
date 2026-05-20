@extends('frontend.layouts.app')
@section('title','Seller Pending')
@section('content')
<div class="container py-16">
  <div class="max-w-2xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm p-8 text-center">
    <h1 class="text-2xl font-semibold">Seller Account Pending</h1>
    <p class="text-slate-600 mt-2">Your seller account is under review. We will notify you after approval.</p>
    <a href="{{ route('home') }}" class="btn-primary mt-5 inline-block">Back to Home</a>
  </div>
</div>
@endsection

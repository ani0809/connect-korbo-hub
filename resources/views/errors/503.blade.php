@extends('frontend.layouts.app')
@section('title','Maintenance')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-24 text-center"><div class="text-6xl">??</div><h1 class="text-3xl font-semibold mt-2">We are Under Maintenance</h1><p class="text-gray-600 mt-2">{{ setting('maintenance_message','Please check back shortly.') }}</p>@if(setting('maintenance_eta'))<p class="text-sm text-gray-500 mt-2">Estimated back: {{ setting('maintenance_eta') }}</p>@endif</div>
@endsection

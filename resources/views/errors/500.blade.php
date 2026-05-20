@extends('frontend.layouts.app')
@section('title','Server Error')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-20 text-center"><div class="text-8xl font-black text-red-600">500</div><h1 class="text-3xl font-semibold mt-2">Something Went Wrong</h1><p class="text-gray-600 mt-2">We are working on it. Please try again later.</p><div class="mt-4 flex justify-center gap-2"><button class="border rounded px-4 py-2" onclick="location.reload()">Refresh</button><a class="border rounded px-4 py-2" href="{{ route('home') }}">Go Home</a></div></div>
@endsection

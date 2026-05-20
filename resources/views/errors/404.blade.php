@extends('frontend.layouts.app')
@section('title','Page Not Found')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-20 text-center"><div class="text-8xl font-black text-blue-600">404</div><h1 class="text-3xl font-semibold mt-2">Oops! Page Not Found</h1><p class="text-gray-600 mt-2">The page you are looking for does not exist or has been moved.</p><form action="{{ route('search') }}" class="mt-4"><input name="q" class="border rounded p-2 w-full" placeholder="Search products"></form><div class="mt-4 flex justify-center gap-2"><a class="border rounded px-4 py-2" href="javascript:history.back()">Go Back</a><a class="border rounded px-4 py-2" href="{{ route('home') }}">Go Home</a></div></div>
@endsection

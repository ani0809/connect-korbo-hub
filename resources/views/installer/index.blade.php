@extends('frontend.layouts.app')
@section('title','Installer')
@section('content')
<div class="container py-16">
  <div class="max-w-2xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm p-8 text-center">
    <h1 class="text-2xl font-semibold">Installer Entry</h1>
    <p class="text-slate-600 mt-2">Use the guided installer route to complete setup.</p>
    <a href="{{ url('/install') }}" class="btn-primary mt-5 inline-block">Open Installer</a>
  </div>
</div>
@endsection

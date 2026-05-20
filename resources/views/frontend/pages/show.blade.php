@extends('frontend.layouts.app')
@section('title', $page->title)
@section('content')
<div class="max-w-4xl mx-auto px-4 py-6"><x-breadcrumb :items="[['name'=>$page->title]]" /><h1 class="text-3xl font-semibold mt-4">{{ $page->title }}</h1><article class="prose max-w-none mt-4">{!! $page->content !!}</article></div>
@endsection

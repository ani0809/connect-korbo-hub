@if($isBlank)
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?? $page->name . ' | ' . setting('site_name') }}</title>
    <meta name="description" content="{{ $page->meta_description }}">
    <style>{!! $content['css'] !!}</style>
    @vite(['resources/css/frontend/app.css'])
</head>
<body>
{!! $content['html'] !!}
@vite(['resources/js/frontend/app.js'])
</body>
</html>
@else
@extends('frontend.layouts.app')

@section('title', $page->meta_title ?? $page->name)
@section('meta_description', $page->meta_description ?? '')

@push('styles')
<style>{!! $content['css'] !!}</style>
@endpush

@section('content')
<div class="builder-page-content">{!! $content['html'] !!}</div>
@endsection
@endif

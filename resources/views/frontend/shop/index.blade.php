@extends('frontend.layouts.app')
@section('title','Shop')
@section('content')
<div class="container py-8">
  <div class="text-sm text-[hsl(var(--muted-foreground))] mb-4 font-medium">Home / Shop</div>
  <div class="grid lg:grid-cols-12 gap-6">
    <aside class="lg:col-span-3 card p-4 h-fit" id="shop-filters">@include('frontend.shop.partials.filters')</aside>
    <main class="lg:col-span-9 space-y-4">
      <div class="card p-3 md:p-4">@include('frontend.shop.partials.sort-bar')</div>
      <div id="shop-products">@include('frontend.shop.partials.products')</div>
    </main>
  </div>
</div>
@vite('resources/js/frontend/shop-filters.js')
@endsection

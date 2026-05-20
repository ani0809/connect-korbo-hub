@extends('frontend.layouts.app')
@section('title', 'Home')
@section('content')
@php($sections = app(\App\Services\BuilderService::class)->getHomepageSections())

@if(empty($sections) || collect($sections)->where('enabled', true)->isEmpty())
  <section class="py-20 bg-gradient-to-br from-[#0E1E30] via-[#142640] to-[#187BA5] text-white">
    <div class="container grid lg:grid-cols-2 gap-10 items-center">
      <div>
        <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs">Premium Collection 2026</span>
        <h1 class="mt-4 text-5xl font-extrabold leading-tight">Build your style with <span class="text-[#9de0d7]">{{ setting('site_name', 'Cibato Commerce') }}</span></h1>
        <p class="mt-4 text-blue-100 max-w-xl">{{ setting('site_description', 'Modern self-hosted eCommerce platform with beautiful UX.') }}</p>
        <div class="mt-7 flex flex-wrap gap-3">
          <a href="{{ route('shop') }}" class="btn-primary">Shop Now</a>
          <a href="{{ route('blog') }}" class="ui-icon-btn !bg-white/10 !border-white/30 !text-white">Explore Blog</a>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div class="rounded-2xl bg-white/10 border border-white/20 p-5"><div class="text-sm text-blue-100">Fast Delivery</div><div class="text-2xl font-bold mt-2">24-48h</div></div>
        <div class="rounded-2xl bg-white/10 border border-white/20 p-5"><div class="text-sm text-blue-100">Happy Buyers</div><div class="text-2xl font-bold mt-2">20k+</div></div>
        <div class="rounded-2xl bg-white/10 border border-white/20 p-5"><div class="text-sm text-blue-100">Curated Products</div><div class="text-2xl font-bold mt-2">5k+</div></div>
        <div class="rounded-2xl bg-white/10 border border-white/20 p-5"><div class="text-sm text-blue-100">Secure Payments</div><div class="text-2xl font-bold mt-2">100%</div></div>
      </div>
    </div>
  </section>

  <section class="py-14">
    <div class="container">
      <div class="flex items-end justify-between mb-5">
        <div>
          <h2 class="text-2xl font-bold">Trending Categories</h2>
          <p class="text-slate-500 text-sm">Discover top picks from every category</p>
        </div>
        <a href="{{ route('shop') }}" class="text-blue-600 text-sm">View all</a>
      </div>
      <div class="grid md:grid-cols-4 gap-4">
        @foreach(['Fashion','Electronics','Home Decor','Sports'] as $cat)
          <a href="{{ route('shop') }}" class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 hover:shadow-lg">
            <div class="h-28 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100"></div>
            <div class="mt-3 font-semibold">{{ $cat }}</div>
            <div class="text-sm text-slate-500">Explore products</div>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  <section class="py-14 bg-slate-50 border-y border-slate-200">
    <div class="container grid lg:grid-cols-3 gap-4">
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"><h3 class="font-semibold">Free Shipping</h3><p class="text-sm text-slate-500 mt-1">On orders over {{ currency_format(100) }}</p></div>
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"><h3 class="font-semibold">Easy Returns</h3><p class="text-sm text-slate-500 mt-1">7-day hassle-free return policy</p></div>
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"><h3 class="font-semibold">24/7 Support</h3><p class="text-sm text-slate-500 mt-1">We are here whenever you need help</p></div>
    </div>
  </section>
@endif

@foreach($sections as $section)
  @if(!($section['enabled'] ?? false)) @continue @endif
  @php($visibility = $section['settings']['visibility'] ?? [])
  <section id="{{ $section['settings']['section_id'] ?? ('section-'.$section['id']) }}" class="homepage-section section-{{ $section['type'] }} {{ !empty($visibility['hide_mobile']) ? 'hide-mobile' : '' }} {{ !empty($visibility['hide_desktop']) ? 'hide-desktop' : '' }}" style="padding-top: {{ $section['settings']['padding_top'] ?? '60px' }}; padding-bottom: {{ $section['settings']['padding_bottom'] ?? '60px' }}; background: {{ $section['settings']['background'] ?? 'transparent' }};">
    @includeIf('frontend.sections.'.$section['type'], ['section' => $section])
  </section>
@endforeach
@endsection

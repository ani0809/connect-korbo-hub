<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $htmlDir ?? (function_exists('current_language_dir') ? current_language_dir() : 'ltr') }}" class="{{ setting('dark_mode_default') === 'dark' ? 'dark' : '' }}" data-theme="{{ setting('dark_mode_default') === 'dark' ? 'dark' : 'light' }}">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}">
  @php($faviconPath = setting('favicon'))
  @if($faviconPath)
  <link rel="icon" type="image/png" href="{{ asset('storage/'.$faviconPath) }}">
  @endif
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  @php($__pageTitle = trim($__env->yieldContent('title')))
  <title>{{ $__pageTitle !== '' ? $__pageTitle.' | ' : '' }}{{ setting('site_name', config('app.name')) }}</title>
  <meta name="description" content="@yield('meta_description', setting('site_description'))">
  <meta name="keywords" content="@yield('meta_keywords')"><meta name="robots" content="@yield('robots', 'index,follow')">
  <link rel="canonical" href="@yield('canonical', url()->current())">
  <style>:root{--color-primary:{{ setting('primary_color','#187BA5') }};--color-primary-hover:{{ setting('primary_hover_color','#146b90') }};--color-secondary:{{ setting('secondary_color','#EAECF0') }};--color-accent:{{ setting('accent_color','#3DB5A6') }};--font-body:'{{ setting('body_font','DM Sans') }}',sans-serif;--font-heading:'{{ setting('heading_font','Space Grotesk') }}',sans-serif;--container-width:{{ setting('container_width','1400px') }};--border-radius:{{ setting('border_radius_global','10px') }};--header-height:{{ setting('header_height','64px') }};}</style>
  @vite(['resources/css/frontend/app.css'])
  @if(!empty($isRtl) && $isRtl)
  @vite(['resources/css/frontend/rtl.css'])
  @endif
  @if(setting('custom_css_desktop'))<style>{{ setting('custom_css_desktop') }}</style>@endif
  @stack('styles')
  {!! setting('header_scripts') !!}
</head>
<body class="antialiased {{ !empty($isRtl) && $isRtl ? 'rtl' : '' }} {{ $bodyClass ?? '' }}" x-data="{mobileMenuOpen:false,searchOpen:false,cartOpen:false,miniCartOpen:false}">
  @if(setting('preloader_enabled', false))<div id="preloader" class="preloader-overlay"><div class="spinner"></div></div>@endif
  @include('frontend.layouts.header')
  @include('frontend.layouts.mobile-menu')
  @include('frontend.layouts.search-overlay')
  <main id="main-content" class="min-h-screen">@yield('content')</main>
  @include('frontend.layouts.footer')
  @if(setting('mobile_bottom_nav', false))@include('frontend.layouts.mobile-bottom-nav')@endif
  @include('frontend.partials.quick-view-modal')
  @include('frontend.partials.compare-bar')
  @include('frontend.partials.cookie-consent')
  @include('frontend.partials.whatsapp-widget')
  <div id="toast-container" class="toast-container"></div>
  @if(setting('scroll_to_top', true))<button id="scroll-top" class="scroll-to-top" type="button" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Scroll to top">&uarr;</button>@endif
  <script>
  window.siteConfig={csrfToken:'{{ csrf_token() }}',baseUrl:'{{ url('/') }}',isLoggedIn:{{ auth()->check()?'true':'false' }},userId:{{ auth()->id() ?? 'null' }},currency:'{{ setting('currency_symbol','$') }}',currencyCode:'{{ setting('currency_code','USD') }}',cartCount:{{ app(\App\Services\CartService::class)->getCount() }},wishlistIds:@json(auth()->check()?\App\Models\Wishlist::where('user_id',auth()->id())->pluck('product_id'):[]),compareIds:@json(session('compare_ids',[]))};
  window.themeSettings={primary:'{{ setting('primary_color','#187BA5') }}',primaryHover:'{{ setting('primary_hover_color','#146b90') }}',secondary:'{{ setting('secondary_color','#EAECF0') }}',accent:'{{ setting('accent_color','#3DB5A6') }}',bodyFont:'{{ setting('body_font','DM Sans') }}',headingFont:'{{ setting('heading_font','Space Grotesk') }}',containerWidth:'{{ setting('container_width','1400px') }}',borderRadius:'{{ setting('border_radius_global','10px') }}',headerHeight:'{{ setting('header_height','64px') }}'};
  </script>
  @vite(['resources/js/frontend/app.js'])
  {!! setting('footer_scripts') !!}
  @stack('scripts')
</body>
</html>

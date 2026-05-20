<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($faviconPath = setting('favicon'))
    @if($faviconPath)
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$faviconPath) }}">
    @endif
    <title>@yield('title', 'Seller Portal') — {{ config('app.name') }}</title>
    @vite(['resources/css/admin/app.css','resources/js/shared/media-picker.js'])
</head>
<body class="seller-panel">
<div class="seller-layout flex min-h-screen">
    <aside class="seller-sidebar">
        <div>
            <div class="seller-sidebar-brand">{{ $currentSeller->shop_name ?? 'Seller Shop' }}</div>
            <span class="seller-sidebar-badge">Seller</span>
        </div>

        <nav class="flex-1 mt-2">
            <div class="seller-nav-label">Overview</div>
            <a href="{{ route('seller.dashboard') }}" class="seller-nav-link {{ request()->routeIs('seller.dashboard') ? 'is-active' : '' }}">Dashboard</a>

            <div class="seller-nav-label">Products</div>
            <a href="{{ route('seller.products.index') }}" class="seller-nav-link {{ request()->routeIs('seller.products.*') ? 'is-active' : '' }}">All Products</a>

            <div class="seller-nav-label">Orders</div>
            <a href="{{ route('seller.orders.index') }}" class="seller-nav-link {{ request()->routeIs('seller.orders.*') ? 'is-active' : '' }}">All Orders</a>

            <div class="seller-nav-label">Shop</div>
            <a href="{{ route('seller.shop.settings') }}" class="seller-nav-link {{ request()->routeIs('seller.shop.*') ? 'is-active' : '' }}">Shop Settings</a>
            <a href="{{ route('seller.reviews.index') }}" class="seller-nav-link {{ request()->routeIs('seller.reviews.*') ? 'is-active' : '' }}">Reviews</a>
            <a href="{{ route('seller.coupons.index') }}" class="seller-nav-link {{ request()->routeIs('seller.coupons.*') ? 'is-active' : '' }}">Coupons</a>

            <div class="seller-nav-label">Finance</div>
            <a href="{{ route('seller.earnings.index') }}" class="seller-nav-link {{ request()->routeIs('seller.earnings.*') ? 'is-active' : '' }}">Earnings</a>
            <a href="{{ route('seller.withdrawal.index') }}" class="seller-nav-link {{ request()->routeIs('seller.withdrawal.*') ? 'is-active' : '' }}">Withdrawals</a>
        </nav>
    </aside>

    <div class="seller-main">
        <header class="seller-topbar">
            <div class="seller-topbar-title">{{ $currentSeller->shop_name ?? 'Seller' }}</div>
            <div class="flex items-center gap-3">
                @if(!empty($currentSeller->shop_slug))
                    <a href="{{ url('/shop/'.$currentSeller->shop_slug) }}" class="store-link seller-store-link" target="_blank" rel="noopener">Visit store</a>
                @endif
                <img class="w-9 h-9 rounded-full object-cover border border-[var(--border-color)] bg-[var(--gray-200)]"
                     src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name) }}"
                     width="36" height="36" alt="">
            </div>
        </header>
        <div class="seller-body">
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>

@if(session('impersonator_id'))
  <div class="impersonation-banner">
    <span>You are viewing the store as a customer (impersonation).</span>
    <form method="post" action="{{ route('impersonate.stop') }}" class="inline">
      @csrf
      <button type="submit" class="impersonation-banner__btn">Return to admin</button>
    </form>
  </div>
@endif
<header class="site-header sticky top-0 z-40 border-b border-[var(--border-color)] bg-[hsl(var(--card))]">
  <div class="hidden md:block border-b border-[hsl(var(--border))] bg-[hsl(var(--background))]">
    <div class="container h-10 flex items-center justify-between text-xs text-[hsl(var(--muted-foreground))]">
      <div class="flex items-center gap-4">
        <span>{{ setting('contact_phone', '+880 1712-345678') }}</span>
        <span>{{ setting('contact_email', 'support@example.com') }}</span>
      </div>
      <div class="flex items-center gap-4">
        <a href="{{ route('seller.register') }}" class="hover:text-[hsl(var(--primary))]">Sell on {{ setting('site_name','Cibato Commerce') }}</a>
        <a href="{{ route('order.track') }}" class="hover:text-[hsl(var(--primary))]">Track Order</a>
        <a href="{{ route('faq') }}" class="hover:text-[hsl(var(--primary))]">Help</a>
      </div>
    </div>
  </div>

  <div class="container site-header-main h-[var(--header-height)] flex items-center justify-between gap-4 md:gap-5">
    <a href="{{ route('home') }}" class="site-brand cc-brand-lockup font-heading text-xl font-bold tracking-tight shrink-0 inline-flex items-center gap-2">
      @if(setting('site_logo'))
        <img src="{{ asset('storage/'.setting('site_logo')) }}" alt="{{ setting('site_name','Cibato Commerce') }}" class="h-8 w-auto object-contain">
      @endif
      <span class="cc-brand-wordmark">{{ setting('site_name','Cibato Commerce') }}</span>
    </a>

    <div class="hidden lg:flex flex-1 max-w-xl min-w-0 pl-2">
      <button type="button" class="w-full text-left form-input h-11 px-4 flex items-center justify-between" @click="searchOpen=true" aria-label="Search">
        <span class="text-[hsl(var(--muted-foreground))]">Search products, brands...</span>
        <span class="text-xs text-[hsl(var(--muted-foreground))]">⌘K</span>
      </button>
    </div>

    <div class="flex items-center gap-2.5 shrink-0">
      <button type="button" class="header-action lg:hidden" @click="searchOpen=true" aria-label="Search">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
      </button>
      @if(\Illuminate\Support\Facades\Route::has('account.wishlist'))
      <a href="{{ route('account.wishlist') }}" class="header-action relative" aria-label="Wishlist">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
      </a>
      @endif
      <a href="{{ route('cart.index') }}" class="header-action relative" aria-label="Cart">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
        <span class="cart-badge">{{ app(\App\Services\CartService::class)->getCount() }}</span>
      </a>
      <a href="{{ route('login') }}" class="header-action hidden sm:inline-flex" aria-label="Account">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
      </a>
      <button type="button" class="header-action lg:hidden font-semibold text-xs px-3 w-auto min-w-[4.5rem]" @click="mobileMenuOpen=true">Menu</button>
    </div>
  </div>

  <div class="hidden md:block border-t border-[hsl(var(--border))]">
    <div class="container h-12 flex items-center justify-between">
      <div class="flex-1">
        @include('frontend.layouts.mega-menu')
      </div>
      <a href="{{ route('shop') }}" class="shrink-0 text-sm font-heading font-semibold text-[hsl(var(--destructive))] hover:opacity-90 transition-opacity whitespace-nowrap">Flash Deals</a>
    </div>
  </div>
</header>

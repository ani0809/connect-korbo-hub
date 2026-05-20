@php
  $labels = [
    'multi_vendor' => 'Multi-vendor (seller system)',
    'wishlist' => 'Wishlist',
    'compare' => 'Compare products',
    'quick_view' => 'Quick view',
    'reviews' => 'Product reviews',
    'qna' => 'Product Q&A',
    'club_points' => 'Club / reward points',
    'wallet' => 'Wallet system',
    'digital_products' => 'Digital products',
    'classified_products' => 'Classified products',
    'auction' => 'Auctions',
    'social_login' => 'Social login',
    'guest_checkout' => 'Guest checkout',
    'price_tracker' => 'Price tracker',
    'waitlist' => 'Waitlist',
    'abandoned_cart' => 'Abandoned cart recovery',
    'blog' => 'Blog',
    'push_notifications' => 'Push notifications',
    'pwa' => 'Progressive Web App (PWA)',
    'dark_mode' => 'Dark mode',
    'rtl' => 'RTL support',
    'customer_chat' => 'Customer chat',
    'whatsapp_order' => 'WhatsApp order',
  ];
  $hints = [
    'multi_vendor' => 'Disabling hides seller-related UI. Seller data is kept.',
    'digital_products' => 'Sell downloadable files.',
    'classified_products' => 'User-posted product ads.',
    'push_notifications' => 'Requires Firebase setup.',
  ];
@endphp
@extends('admin.layouts.app')
@section('title', 'Feature activation')
@section('content')
  <div class="mb-6">
    <h2 class="text-xl font-semibold">Feature activation</h2>
    <p class="text-slate-600 text-sm mt-1">Enable or disable features to customize your store.</p>
  </div>

  <form method="post" action="{{ route('admin.features.save') }}" class="space-y-6 max-w-3xl" id="feature-form">
    @csrf

    <section class="bg-white border rounded-xl p-5 shadow-sm">
      <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b">Shopping features</h3>
      <div class="space-y-4">
        @foreach (['wishlist','compare','quick_view','reviews','qna'] as $f)
          <label class="flex items-start justify-between gap-4 cursor-pointer">
            <span>
              <span class="font-medium block">{{ $labels[$f] ?? $f }}</span>
            </span>
            <span class="feature-toggle shrink-0">
              <input type="hidden" name="features[{{ $f }}]" value="0">
              <input type="checkbox" name="features[{{ $f }}]" value="1" class="feature-toggle-input sr-only" {{ ($features[$f] ?? false) ? 'checked' : '' }} data-feature="{{ $f }}">
              <span class="feature-toggle-ui" aria-hidden="true"></span>
            </span>
          </label>
        @endforeach
      </div>
    </section>

    <section class="bg-white border rounded-xl p-5 shadow-sm">
      <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b">Customer features</h3>
      <div class="space-y-4">
        @foreach (['club_points','wallet','social_login','guest_checkout','price_tracker','waitlist'] as $f)
          <label class="flex items-start justify-between gap-4 cursor-pointer">
            <span class="font-medium">{{ $labels[$f] ?? $f }}</span>
            <span class="feature-toggle shrink-0">
              <input type="hidden" name="features[{{ $f }}]" value="0">
              <input type="checkbox" name="features[{{ $f }}]" value="1" class="feature-toggle-input sr-only" {{ ($features[$f] ?? false) ? 'checked' : '' }} data-feature="{{ $f }}">
              <span class="feature-toggle-ui" aria-hidden="true"></span>
            </span>
          </label>
        @endforeach
      </div>
    </section>

    <section class="bg-white border rounded-xl p-5 shadow-sm">
      <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b">Marketplace features</h3>
      <label class="flex items-start justify-between gap-4 cursor-pointer">
        <span>
          <span class="font-medium block">{{ $labels['multi_vendor'] }}</span>
          @if(!empty($hints['multi_vendor']))
            <span class="text-xs text-amber-700 block mt-1">⚠ {{ $hints['multi_vendor'] }}</span>
          @endif
        </span>
        <span class="feature-toggle shrink-0">
          <input type="hidden" name="features[multi_vendor]" value="0">
          <input type="checkbox" name="features[multi_vendor]" value="1" class="feature-toggle-input sr-only" {{ ($features['multi_vendor'] ?? false) ? 'checked' : '' }} data-feature="multi_vendor">
          <span class="feature-toggle-ui" aria-hidden="true"></span>
        </span>
      </label>
    </section>

    <section class="bg-white border rounded-xl p-5 shadow-sm">
      <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b">Product types</h3>
      <div class="space-y-4">
        @foreach (['digital_products','classified_products','auction'] as $f)
          <label class="flex items-start justify-between gap-4 cursor-pointer">
            <span>
              <span class="font-medium block">{{ $labels[$f] ?? $f }}</span>
              @if(!empty($hints[$f]))<span class="text-xs text-slate-500 block mt-0.5">{{ $hints[$f] }}</span>@endif
            </span>
            <span class="feature-toggle shrink-0">
              <input type="hidden" name="features[{{ $f }}]" value="0">
              <input type="checkbox" name="features[{{ $f }}]" value="1" class="feature-toggle-input sr-only" {{ ($features[$f] ?? false) ? 'checked' : '' }} data-feature="{{ $f }}">
              <span class="feature-toggle-ui" aria-hidden="true"></span>
            </span>
          </label>
        @endforeach
      </div>
    </section>

    <section class="bg-white border rounded-xl p-5 shadow-sm">
      <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b">Marketing</h3>
      <div class="space-y-4">
        @foreach (['blog','abandoned_cart'] as $f)
          <label class="flex items-start justify-between gap-4 cursor-pointer">
            <span class="font-medium">{{ $labels[$f] ?? $f }}</span>
            <span class="feature-toggle shrink-0">
              <input type="hidden" name="features[{{ $f }}]" value="0">
              <input type="checkbox" name="features[{{ $f }}]" value="1" class="feature-toggle-input sr-only" {{ ($features[$f] ?? false) ? 'checked' : '' }} data-feature="{{ $f }}">
              <span class="feature-toggle-ui" aria-hidden="true"></span>
            </span>
          </label>
        @endforeach
      </div>
    </section>

    <section class="bg-white border rounded-xl p-5 shadow-sm">
      <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b">Communication</h3>
      <div class="space-y-4">
        @foreach (['whatsapp_order','push_notifications','customer_chat'] as $f)
          <label class="flex items-start justify-between gap-4 cursor-pointer">
            <span>
              <span class="font-medium block">{{ $labels[$f] ?? $f }}</span>
              @if(!empty($hints[$f]))<span class="text-xs text-slate-500 block mt-0.5">{{ $hints[$f] }}</span>@endif
            </span>
            <span class="feature-toggle shrink-0">
              <input type="hidden" name="features[{{ $f }}]" value="0">
              <input type="checkbox" name="features[{{ $f }}]" value="1" class="feature-toggle-input sr-only" {{ ($features[$f] ?? false) ? 'checked' : '' }} data-feature="{{ $f }}">
              <span class="feature-toggle-ui" aria-hidden="true"></span>
            </span>
          </label>
        @endforeach
      </div>
    </section>

    <section class="bg-white border rounded-xl p-5 shadow-sm">
      <h3 class="font-semibold text-slate-800 mb-4 pb-2 border-b">Advanced</h3>
      <div class="space-y-4">
        @foreach (['pwa','dark_mode','rtl'] as $f)
          <label class="flex items-start justify-between gap-4 cursor-pointer">
            <span class="font-medium">{{ $labels[$f] ?? $f }}</span>
            <span class="feature-toggle shrink-0">
              <input type="hidden" name="features[{{ $f }}]" value="0">
              <input type="checkbox" name="features[{{ $f }}]" value="1" class="feature-toggle-input sr-only" {{ ($features[$f] ?? false) ? 'checked' : '' }} data-feature="{{ $f }}">
              <span class="feature-toggle-ui" aria-hidden="true"></span>
            </span>
          </label>
        @endforeach
      </div>
    </section>

    <div class="sticky bottom-4 pt-4 border-t border-slate-200">
      <button type="submit" class="btn-primary">Save feature settings</button>
    </div>
  </form>
@endsection

@push('styles')
<style>
  .feature-toggle { position: relative; width: 3rem; height: 1.75rem; }
  .feature-toggle-ui {
    display: block; width: 100%; height: 100%; border-radius: 9999px;
    background: #cbd5e1; transition: background .2s ease;
  }
  .feature-toggle-input:checked + .feature-toggle-ui { background: #2563eb; }
  .feature-toggle-ui::after {
    content: ''; position: absolute; top: 3px; left: 3px;
    width: 1.25rem; height: 1.25rem; border-radius: 9999px;
    background: #fff; box-shadow: 0 1px 2px rgb(0 0 0 / 0.15);
    transition: transform .2s ease;
  }
  .feature-toggle-input:checked + .feature-toggle-ui::after { transform: translateX(1.1rem); }
  .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
</style>
@endpush

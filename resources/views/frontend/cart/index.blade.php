@extends('frontend.layouts.app')
@section('title','Cart')
@section('content')
<div class="container py-8 grid lg:grid-cols-12 gap-6">
  <section class="lg:col-span-8 card p-5">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Shopping Cart</h1>
      <a href="{{ route('shop') }}" class="text-sm link-primary">Continue shopping</a>
    </div>

    <div class="space-y-3" id="cart-items">
      @forelse($items as $item)
      <div class="grid grid-cols-12 gap-3 border border-[hsl(var(--border))] rounded-xl p-3 bg-[hsl(var(--card))]" data-item-id="{{ $item->id }}">
        <div class="col-span-3 md:col-span-2"><img src="{{ $item->product->thumbnail_url }}" class="w-20 h-20 object-cover rounded-lg border"></div>
        <div class="col-span-9 md:col-span-5">
          <div class="font-semibold text-[hsl(var(--foreground))]">{{ $item->product->name }}</div>
          <div class="text-xs text-muted mt-1">@foreach($item->variant?->attributeValues ?? [] as $av){{ $av->attribute?->name }}: {{ $av->value }} @endforeach</div>
          <button class="text-[var(--color-danger)] text-sm mt-2 remove-item-btn" data-item-id="{{ $item->id }}">Remove</button>
        </div>
        <div class="col-span-4 md:col-span-2 text-sm font-medium">{{ currency_format((float)$item->unit_price) }}</div>
        @php
          $minQty = max(1, (int) ($item->product->min_purchase_qty ?? 1));
          $maxQty = $item->product->isSoldIndividually() ? 1 : (int) ($item->product->max_purchase_qty ?? 0);
        @endphp
        <div class="col-span-5 md:col-span-2">
          <div class="flex items-center gap-1">
            <button class="qty-btn border rounded px-2" data-delta="-1" data-item-id="{{ $item->id }}" data-min="{{ $minQty }}" data-max="{{ $maxQty }}" @disabled($item->quantity <= $minQty)>-</button>
            <input class="qty-input w-12 text-center border rounded" value="{{ $item->quantity }}" data-min="{{ $minQty }}" data-max="{{ $maxQty }}" @readonly($item->product->isSoldIndividually())>
            <button class="qty-btn border rounded px-2" data-delta="1" data-item-id="{{ $item->id }}" data-min="{{ $minQty }}" data-max="{{ $maxQty }}" @disabled($item->product->isSoldIndividually() || ($maxQty > 0 && $item->quantity >= $maxQty))>+</button>
          </div>
        </div>
        <div class="col-span-3 md:col-span-1 item-total text-right font-semibold">{{ currency_format((float)$item->unit_price * $item->quantity) }}</div>
      </div>
      @empty
      <div class="empty-state">Your cart is empty.</div>
      @endforelse
    </div>

    @if(!empty($summary['applied_promotions']))
    <div class="cart-promotions-applied">
      <h4 class="promotions-title">Applied Promotions</h4>
      @foreach($summary['promotion_messages'] as $msg)
      <div class="promotion-applied-item"><span class="promo-check">✓</span>{{ $msg }}</div>
      @endforeach
    </div>
    @endif

    @if(!empty($summary['free_items']))
    <div class="free-items-section">
      <h4 class="free-items-title">Free Items Added</h4>
      @foreach($summary['free_items'] as $item)
      <div class="free-item-row"><span>{{ $item['product_name'] }} × {{ $item['quantity'] }}</span><span class="free-badge">FREE</span></div>
      @endforeach
    </div>
    @endif

    @include('frontend.partials.promotion-hints', ['cartTotal' => $summary['subtotal']])

    <div class="mt-5 p-4 border border-[hsl(var(--border))] rounded-xl bg-[hsl(var(--muted))]">
      <div class="text-sm mb-2">Free shipping progress</div>
      @php($min=(float)config('shop.free_shipping_min',100))
      @php($pct=min(100, ($summary['subtotal'] / max(1,$min))*100))
      <div class="h-2 bg-[hsl(var(--muted))] rounded-full"><div id="free-ship-bar" class="h-2 bg-[hsl(var(--primary))] rounded-full" style="width: {{ $pct }}%"></div></div>
      <div id="free-ship-msg" class="text-sm mt-2 text-muted">
        @if(!empty($summary['free_shipping']))
          FREE shipping unlocked 🎉
        @else
          Add {{ currency_format(max(0,$min-$summary['subtotal'])) }} more for FREE shipping.
        @endif
      </div>
    </div>

    <div class="mt-5 flex gap-2"><input id="coupon-code" class="border rounded-xl px-3 py-2 flex-1" placeholder="Enter coupon code"><button id="apply-coupon" class="ui-icon-btn">Apply</button></div>
  </section>

  <aside class="lg:col-span-4">
    <div class="card p-5 sticky top-24">
      <h3 class="font-heading font-semibold text-[hsl(var(--foreground))] mb-3">Order Summary</h3>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between text-[hsl(var(--muted-foreground))]"><span>Subtotal</span><strong class="text-[hsl(var(--foreground))]" id="sum-subtotal">{{ currency_format($summary['subtotal']) }}</strong></div>
        <div class="flex justify-between text-[hsl(var(--muted-foreground))]"><span>Shipping</span><strong class="text-[hsl(var(--foreground))]" id="sum-shipping">{{ currency_format($summary['shipping']) }}</strong></div>
        <div class="flex justify-between text-[hsl(var(--muted-foreground))]"><span>Tax</span><strong class="text-[hsl(var(--foreground))]" id="sum-tax">{{ currency_format($summary['tax']) }}</strong></div>
        <div class="flex justify-between text-[hsl(var(--muted-foreground))]"><span>Discount</span><strong class="text-[hsl(var(--foreground))]" id="sum-discount">-{{ currency_format($summary['discount']) }}</strong></div>
        <div class="h-px bg-[hsl(var(--border))] my-2"></div>
        <div class="flex justify-between items-center text-base"><span class="font-medium text-[hsl(var(--foreground))]">Total</span><strong class="font-heading text-xl text-[hsl(var(--foreground))]" id="sum-total">{{ currency_format($summary['total']) }}</strong></div>
      </div>
      <a href="{{ route('checkout.index') }}" class="mt-4 block text-center btn-primary">Proceed to Checkout</a>
    </div>
  </aside>
</div>
@if(($crossSellProducts ?? collect())->isNotEmpty())
<div class="container pb-8">
  <section class="card p-5">
    <h2 class="text-xl font-bold mb-4">You may also like</h2>
    <div class="grid md:grid-cols-4 gap-4">
      @foreach($crossSellProducts as $crossSell)
      <article class="border border-[hsl(var(--border))] rounded-xl p-3 bg-[hsl(var(--card))]">
        <a href="{{ route('product.show', $crossSell->slug) }}" class="block">
          <img src="{{ $crossSell->thumbnail_url }}" alt="{{ $crossSell->name }}" class="w-full aspect-square object-cover rounded-lg border mb-3">
          <h3 class="font-medium text-[hsl(var(--foreground))]">{{ $crossSell->name }}</h3>
          <div class="text-sm text-[hsl(var(--muted-foreground))] mt-1">{{ currency_format((float) ($crossSell->main_price ?? 0)) }}</div>
        </a>
      </article>
      @endforeach
    </div>
  </section>
</div>
@endif
@vite('resources/js/frontend/cart.js')
@endsection

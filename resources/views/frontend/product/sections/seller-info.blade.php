<div class="seller-info-card card p-4">
    <h3 class="font-heading text-lg font-semibold text-[hsl(var(--foreground))]">{{ $product->seller->shop_name ?? 'Seller' }}</h3>
    <p class="mt-2 text-sm text-[hsl(var(--muted-foreground))]">{{ $product->seller->shop_description ?? 'Trusted marketplace seller.' }}</p>
    @if(!empty($product->seller->shop_slug))
        <a href="{{ route('seller.shop.show', $product->seller->shop_slug) }}" class="btn-secondary mt-3">Visit Store</a>
    @endif
</div>

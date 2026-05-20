<div class="product-layout-c product-layout-a">
    <div>
        @include('frontend.product.layouts._b', compact('product'))
    </div>
    <aside class="product-info-col" style="position:sticky;top:90px">
        <h1 class="product-page-title">{{ $product->name }}</h1>
        <p class="product-short-desc">{!! $product->short_description !!}</p>
        @include('frontend.product.partials.variation-selectors', compact('product', 'variationAttributes'))
        <div class="product-price-block">
            <span class="current-price" x-text="currentPrice">{{ currency_format((float) ($product->main_price ?? 0)) }}</span>
        </div>
        <button type="button" class="btn-atc-main" @click="addToCart()" :disabled="!inStock">🛒 Add to Cart</button>
        <button type="button" class="btn-buy-now-main" @click="buyNow()" :disabled="!inStock">⚡ Buy Now</button>
    </aside>
</div>

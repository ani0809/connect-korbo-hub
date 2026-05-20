@extends('frontend.layouts.app')

@section('title', $product->meta_title ?? $product->name)

@push('scripts')
<script type="application/ld+json">{!! app(\App\Services\SeoService::class)->getProductSchema($product) !!}</script>
@endpush

@section('content')
@php
    $layout = setting('product_page_layout', 'layout-a');
    $primaryVariant = $product->primaryVariant();
    $baseVariant = $product->resolvePurchasableVariant($primaryVariant);
    $baseCurrentPrice = (float) (($baseVariant?->sale_price && (float) $baseVariant->sale_price > 0) ? $baseVariant->sale_price : ($baseVariant?->price ?? $product->main_price ?? 0));
    $baseOriginalPrice = (float) ($baseVariant?->price ?? $product->main_price ?? 0);
    $initialInStock = $product->type === 'variable'
        ? collect($variationData)->contains(fn ($variant) => (bool) ($variant['is_available'] ?? false))
        : $product->canPurchase(1, $baseVariant);
@endphp

<div class="product-breadcrumb-bar bg-[hsl(var(--background))] border-y border-[hsl(var(--border))]">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span class="sep">›</span>
            <a href="{{ url('/shop') }}">Shop</a>
            @if($product->category)
                <span class="sep">›</span>
                <a href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a>
            @endif
            <span class="sep">›</span>
            <span>{{ $product->name }}</span>
        </nav>
    </div>
</div>

<div
    class="product-page-section"
    x-data="ProductPage({
        productId: {{ $product->id }},
        productType: @js($product->type),
        variants: @js($variationData),
        variationAttributes: @js($variationAttributes),
        inStock: {{ $initialInStock ? 'true' : 'false' }},
        currency: '{{ setting('currency_code', 'USD') }}',
        basePrice: {{ $baseCurrentPrice }},
        baseOriginalPrice: {{ $baseOriginalPrice }},
        minPurchaseQty: {{ (int) ($product->min_purchase_qty ?? 1) }},
        maxPurchaseQtyLimit: {{ (int) ($product->max_purchase_qty ?? 0) }},
        soldIndividually: {{ $product->isSoldIndividually() ? 'true' : 'false' }},
        allowsBackorders: {{ $product->allowsBackorders() ? 'true' : 'false' }},
        manageStock: {{ $product->managesStock() ? 'true' : 'false' }},
        baseStock: {{ (int) ($baseVariant?->stock ?? $product->stock ?? 0) }},
        backorderMode: @js($product->backorderMode())
    })"
>
    <div class="container">
        @if($layout === 'layout-b')
            @include('frontend.product.layouts._b', compact('product'))
        @elseif($layout === 'layout-c')
            @include('frontend.product.layouts._c', compact('product'))
        @else
            @include('frontend.product.layouts._a', compact('product'))
        @endif
    </div>
</div>

<div class="product-tabs-section" x-data="{ activeTab: window.location.hash?.replace('#', '') || 'description' }">
    <div class="container">
        <div class="product-tabs-nav">
            <button class="product-tab-btn" :class="{ 'active': activeTab === 'description' }" @click="activeTab = 'description'; window.location.hash = 'description'">Description</button>
            <button class="product-tab-btn" :class="{ 'active': activeTab === 'additional' }" @click="activeTab = 'additional'; window.location.hash = 'additional'">Additional Info</button>
            @if(feature('reviews'))
                <button class="product-tab-btn" :class="{ 'active': activeTab === 'reviews' }" @click="activeTab = 'reviews'; window.location.hash = 'reviews'">Reviews ({{ (int) ($product->total_reviews ?? 0) }})</button>
            @endif
            @if(feature('qna'))
                <button class="product-tab-btn" :class="{ 'active': activeTab === 'qna' }" @click="activeTab = 'qna'; window.location.hash = 'qna'">Q&A</button>
            @endif
            @if($product->seller)
                <button class="product-tab-btn" :class="{ 'active': activeTab === 'seller' }" @click="activeTab = 'seller'; window.location.hash = 'seller'">About Seller</button>
            @endif
        </div>

        <div class="product-tab-content card p-5">
            <div x-show="activeTab === 'description'" x-transition>
                <div class="product-description-content">{!! $product->description !!}</div>
            </div>
            <div x-show="activeTab === 'additional'" x-transition>
                <table class="specs-table">
                    <tbody>
                        @if($product->sku)<tr><th>SKU</th><td>{{ $product->sku }}</td></tr>@endif
                        @if($product->brand)<tr><th>Brand</th><td>{{ $product->brand->name }}</td></tr>@endif
                        @if($product->weight)<tr><th>Weight</th><td>{{ $product->weight }} kg</td></tr>@endif
                        @if($product->category)<tr><th>Category</th><td>{{ $product->category->name }}</td></tr>@endif
                        @foreach(($visibleAttributes ?? []) as $attribute)<tr><th>{{ $attribute['name'] }}</th><td>{{ implode(', ', $attribute['values'] ?? []) }}</td></tr>@endforeach
                        @foreach(($product->tags ?? []) as $tag)<tr><th>Tag</th><td>{{ $tag }}</td></tr>@endforeach
                    </tbody>
                </table>
            </div>
            <div x-show="activeTab === 'reviews'" x-transition>@include('frontend.product.sections.reviews', ['product' => $product, 'pendingReviewOrderItem' => $pendingReviewOrderItem ?? null])</div>
            <div x-show="activeTab === 'qna'" x-transition>@include('frontend.product.sections.qna', compact('product'))</div>
            @if($product->seller)
                <div x-show="activeTab === 'seller'" x-transition>@include('frontend.product.sections.seller-info', compact('product'))</div>
            @endif
        </div>
    </div>
</div>

@if(($relatedProducts ?? collect())->isNotEmpty())
<section class="related-products-section">
    <div class="container">
        <h2 class="section-title">Related Products</h2>
        <div class="products-grid cols-4">
            @foreach($relatedProducts as $related)
                <article class="product-card card">
                    <a href="{{ route('product.show', $related->slug) }}">
                        <img src="{{ $related->thumbnail_url }}" alt="{{ $related->name }}">
                        <h3>{{ $related->name }}</h3>
                        <div>{{ currency_format((float) ($related->main_price ?? 0)) }}</div>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@include('frontend.product.sections.recently-viewed', ['products' => $recentlyViewedProducts ?? collect()])

@if(($frequentlyBought ?? collect())->isNotEmpty())
<section class="frequently-bought-section">
    <div class="container">
        <h2 class="section-title">Frequently Bought Together</h2>
        @include('frontend.product.sections.frequently-bought', ['product' => $product, 'frequentlyBought' => $frequentlyBought])
    </div>
</section>
@endif

@vite('resources/js/frontend/product-page.js')
@endsection

<div class="product-layout-a">
    <div class="product-gallery-col" x-data="ProductGallery(@js(($product->images ?? collect())->map(fn($img) => asset('storage/'.($img->image ?? '')))->filter()->values()))">
        <div class="product-main-image" id="product-zoom-container" @mousemove="handleZoom($event)" @mouseleave="zoomActive = false">
            <img :src="currentImage" :alt="'{{ addslashes($product->name) }}'" class="main-product-image" id="main-product-image">
            <div class="zoom-lens" x-show="zoomActive" :style="zoomLensStyle"></div>
            <div class="zoom-result" x-show="zoomActive" x-ref="zoomResult"></div>
            <button class="gallery-arrow prev" @click="prevImage()" x-show="images.length > 1">‹</button>
            <button class="gallery-arrow next" @click="nextImage()" x-show="images.length > 1">›</button>
        </div>
        <div class="thumbnail-strip" x-show="images.length > 1">
            <template x-for="(img, index) in images" :key="index">
                <button type="button" class="thumbnail-item" :class="{ active: currentIndex === index }" @click="setImage(index)">
                    <img :src="img" alt="">
                </button>
            </template>
        </div>

        <div class="product-share">
            <span class="share-label">Share:</span>
            <div class="share-buttons">
                <a href="https://facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="share-btn facebook">f</a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($product->name) }}" target="_blank" rel="noopener" class="share-btn twitter">𝕏</a>
                <a href="https://wa.me/?text={{ urlencode($product->name . ' ' . url()->current()) }}" target="_blank" rel="noopener" class="share-btn whatsapp">W</a>
                <a href="https://www.pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&description={{ urlencode($product->name) }}" target="_blank" rel="noopener" class="share-btn pinterest">P</a>
            </div>
        </div>
    </div>

    <div class="product-info-col" id="product-info-sticky">
        @include('frontend.partials.promotion-badges', ['product' => $product])
        @include('frontend.partials.promotion-timer', ['product' => $product])
        @include('frontend.partials.buy-x-get-y-info', ['product' => $product])
        @if($product->brand)
            <div class="product-brand-badge"><span class="brand-text-link">{{ $product->brand->name }}</span></div>
        @endif
        <h1 class="product-page-title">{{ $product->name }}</h1>
        <div class="product-meta-row">
            @if(($product->total_reviews ?? 0) > 0)
                <div class="product-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="star {{ $i <= round((float) $product->rating) ? 'filled' : '' }}">★</span>
                    @endfor
                    <a href="#reviews" class="reviews-link">({{ $product->total_reviews }} reviews)</a>
                </div>
            @endif
            @if($product->sku)<span class="meta-divider">|</span><span class="product-sku">SKU: <strong>{{ $product->sku }}</strong></span>@endif
        </div>

        <div class="product-price-block">
            <div class="price-display">
                <span class="current-price" x-text="currentPrice">{{ currency_format((float) ($product->main_price ?? 0)) }}</span>
                @if((float) ($product->price ?? 0) > (float) ($product->main_price ?? 0))
                    <span class="original-price" x-text="originalPrice">{{ currency_format((float) ($product->price ?? 0)) }}</span>
                @endif
            </div>
        </div>

        @if($product->short_description)<div class="product-short-desc">{!! $product->short_description !!}</div>@endif
        @include('frontend.product.partials.variation-selectors', compact('product', 'variationAttributes'))
        <div class="stock-status-bar"><span :class="{ 'in-stock': inStock, 'out-of-stock': !inStock }" x-text="stockText"></span></div>

        <div class="atc-section">
            <div class="qty-wrapper">
                <button type="button" class="qty-minus" @click="qty > minQty ? qty-- : null" :disabled="qty <= minQty">−</button>
                <input type="number" x-model.number="qty" :min="minQty" :max="maxQty" class="qty-input">
                <button type="button" class="qty-plus" @click="qty < maxQty ? qty++ : null" :disabled="qty >= maxQty">+</button>
            </div>
            <button type="button" class="btn-atc-main" @click="addToCart()" :disabled="!inStock || addingToCart">
                <span x-show="!addingToCart">🛒 Add to Cart</span>
                <span x-show="addingToCart">Adding...</span>
            </button>
        </div>
        <button type="button" class="btn-buy-now-main" @click="buyNow()" :disabled="!inStock">⚡ Buy Now</button>
    </div>
</div>

<div class="sticky-atc-bar" x-show="showStickyBar">
    <div class="container">
        <div class="sticky-atc-inner">
            <div class="sticky-product-info">
                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="sticky-thumb">
                <div><div class="sticky-name">{{ $product->name }}</div><div class="sticky-price" x-text="currentPrice">{{ currency_format((float) ($product->main_price ?? 0)) }}</div></div>
            </div>
            <button type="button" class="btn-atc-sticky" @click="addToCart()" :disabled="!inStock">🛒 Add to Cart</button>
        </div>
    </div>
</div>

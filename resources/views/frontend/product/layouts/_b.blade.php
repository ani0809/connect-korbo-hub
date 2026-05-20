<div class="product-layout-b">
    <div class="product-gallery-col" x-data="ProductGallery(@js(($product->images ?? collect())->map(fn($img) => asset('storage/'.($img->image ?? '')))->filter()->values()))">
        <div class="product-main-image">
            <img :src="currentImage" alt="{{ $product->name }}" class="main-product-image">
        </div>
        <div class="thumbnail-strip" x-show="images.length > 1">
            <template x-for="(img, index) in images" :key="index">
                <button class="thumbnail-item" type="button" :class="{ active: currentIndex === index }" @click="setImage(index)">
                    <img :src="img" alt="">
                </button>
            </template>
        </div>
    </div>
    <div class="product-info-col">
        <h1 class="product-page-title">{{ $product->name }}</h1>
        <div class="current-price" x-text="currentPrice">{{ currency_format((float) ($product->main_price ?? 0)) }}</div>
        <div class="product-short-desc">{!! $product->short_description !!}</div>
        @include('frontend.product.partials.variation-selectors', compact('product', 'variationAttributes'))
        <div class="atc-section">
            <div class="qty-wrapper">
                <button type="button" class="qty-minus" @click="qty > minQty ? qty-- : null" :disabled="qty <= minQty">−</button>
                <input type="number" class="qty-input" x-model.number="qty" :min="minQty" :max="maxQty">
                <button type="button" class="qty-plus" @click="qty < maxQty ? qty++ : null">+</button>
            </div>
            <button type="button" class="btn-atc-main" @click="addToCart()" :disabled="!inStock || addingToCart">🛒 Add to Cart</button>
        </div>
    </div>
</div>

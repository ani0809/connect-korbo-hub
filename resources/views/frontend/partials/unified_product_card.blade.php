@php
    $cardProduct = $product;
    /* Enforce one global product-card design across all pages/themes. */
    $cardPaddingClass = 'px-0';
    $imageAspectRatio = '1 / 1';
    $imageFillCover = false;
    $productUrl = $product_url ?? route('product', $cardProduct->slug);
    if (!isset($product_url) && $cardProduct->auction_product == 1) {
        $productUrl = route('auction-product', $cardProduct->slug);
    }
    $colors = is_string($cardProduct->colors) ? json_decode($cardProduct->colors, true) : $cardProduct->colors;
    $attributes = is_string($cardProduct->attributes) ? json_decode($cardProduct->attributes, true) : $cardProduct->attributes;
@endphp

<div class="cibato-card-box hov-scale-img h-auto {{ $cardPaddingClass }}" style="isolation: isolate;">
    <div class="img rounded-2 overflow-hidden position-relative image-hover-effect" style="width: 100%; max-width: 100%; aspect-ratio: {{ $imageAspectRatio }}; margin: 0 auto;">
        <a href="{{ $productUrl }}" title="" class="{{ $imageFillCover ? 'd-block position-absolute w-100 h-100 top-0 left-0' : '' }}" style="{{ $imageFillCover ? 'overflow: hidden;' : '' }}">
            <img class="lazyload has-transition product-main-image {{ $imageFillCover ? 'w-100 h-100' : 'img-fit m-auto' }}"
                src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($cardProduct->thumbnail_img) }}"
                alt="{{ $cardProduct->name }}"
                style="{{ $imageFillCover ? 'object-fit: cover; object-position: center;' : '' }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">

            <img class="lazyload has-transition product-main-image product-hover-image position-absolute {{ $imageFillCover ? 'w-100 h-100' : 'img-fit m-auto' }}"
                src="{{ get_first_product_image($cardProduct->thumbnail, $cardProduct->photos) }}" alt="{{ $cardProduct->name }}"
                title=""
                style="{{ $imageFillCover ? 'top:0;left:0;right:0;bottom:0;object-fit:cover;object-position:center;' : '' }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
        </a>

        <div class="d-none d-sm-block absolute-top-right cibato-p-hov-icon">
            <a href="javascript:void(0)" onclick="addToWishList({{ $cardProduct->id }})"
                data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.4" viewBox="0 0 16 14.4">
                    <g transform="translate(-3.05 -4.178)">
                        <path d="M11.3,5.507l-.247.246L10.8,5.506A4.538,4.538,0,1,0,4.38,11.919l.247.247,6.422,6.412,6.422-6.412.247-.247A4.538,4.538,0,1,0,11.3,5.507Z" transform="translate(0 0)" fill="#919199" />
                    </g>
                </svg>
            </a>
            <a href="javascript:void(0)" onclick="addToCompare({{ $cardProduct->id }})"
                data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                    <path d="M18.037,5.547v.8a.8.8,0,0,1-.8.8H7.221a.4.4,0,0,0-.4.4V9.216a.642.642,0,0,1-1.1.454L2.456,6.4a.643.643,0,0,1,0-.909L5.723,2.227a.642.642,0,0,1,1.1.454V4.342a.4.4,0,0,0,.4.4H17.234a.8.8,0,0,1,.8.8Zm-3.685,4.86a.642.642,0,0,0-1.1.454v1.661a.4.4,0,0,1-.4.4H2.84a.8.8,0,0,0-.8.8v.8a.8.8,0,0,0,.8.8H12.854a.4.4,0,0,1,.4.4V17.4a.642.642,0,0,0,1.1.454l3.267-3.268a.643.643,0,0,0,0-.909Z" transform="translate(-2.037 -2.038)" fill="#919199" />
                </svg>
            </a>
        </div>

        @if ((is_array($colors) && count($colors) > 0) || (is_array($attributes) && count($attributes) > 0))
            <a class="cart-btn absolute-bottom-left w-100 h-35px cibato-p-hov-icon text-white fs-13 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center"
                href="javascript:void(0)" onclick="showAddToCartRightCanvas({{ $cardProduct->id }})">
                <span class="cart-btn-text">{{ translate('Select Option') }}</span>
                <span><i class="las la-sliders-h" style="font-size: 1.4rem;"></i></span>
            </a>
        @else
            <a class="cart-btn absolute-bottom-left w-100 h-35px cibato-p-hov-icon text-white fs-13 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center"
                href="javascript:void(0)" @if (Auth::check() || get_Setting('guest_checkout_activation') == 1) onclick="addToCartSingleProduct({{ $cardProduct->id }})" @else onclick="showLoginModal()" @endif>
                <span class="cart-btn-text">{{ translate('Add to Cart') }}</span>
                <span><i class="las la-2x la-shopping-cart"></i></span>
            </a>
        @endif
    </div>
    <div class="mt-2 pr-3">
        <h3 class="fw-400 fs-13 text-truncate-2 lh-1-4 mb-1 h-35px">
            <a href="{{ $productUrl }}" class="text-reset hov-text-primary">{{ $cardProduct->name }}</a>
        </h3>
        <div class="fw-700 fs-14 mb-1 mt-2 d-flex align-items-center flex-wrap">
            <del class="fw-700 opacity-60 mr-1">{{ home_base_price($cardProduct) }}</del>
            <span class="text-primary">{{ home_discounted_base_price($cardProduct) }}</span>
        </div>
    </div>
</div>

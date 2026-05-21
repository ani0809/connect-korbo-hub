@if (get_setting('classified_product') == 1)
    @php $classified_products = get_home_page_classified_products(); @endphp
    @if (count($classified_products) > 0)
        <section class="pt-32px pb-26px my-4" style="background: {{ get_setting('classified_bg_color', '#f5f5f5') }}">
            <div class="container">
                <div class="d-sm-flex">
                    <div class="w-100 overflow-hidden">
                        <div class="d-flex align-items-baseline justify-content-between">
                            <div class="mb-sm-0 ml-3 pb-2">
                                <h4 class="fs-16 fw-700 mb-0">{{ translate('Classified Ads') }}</h4>
                                <p class="fs-12 mb-0 fw-400">{{ translate('products') }} ({{ count($classified_products) }})</p>
                            </div>
                            <a class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" href="{{ route('customer.products') }}">
                                <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                                <span class="fs-12 mr-2 text">View All</span>
                            </a>
                        </div>
                        <div class="cibato-carousel arrow-x-0 arrow-inactive-none" data-items="7" data-xxl-items="7" data-xl-items="6" data-lg-items="5" data-md-items="4" data-sm-items="4" data-xs-items="3" data-arrows="false" data-dots="false" data-autoplay="true" data-infinite="true">
                            @foreach ($classified_products as $product)
                                <div class="px-3">
                                    <div class="img h-100px w-100px h-md-150px w-md-150px h-lg-170px w-lg-170px rounded-2 overflow-hidden mx-auto position-relative image-hover-effect">
                                        <a href="{{ route('customer.product', $product->slug) }}" title="{{ $product->getTranslation('name') }}">
                                            <img class="lazyload img-fit m-auto has-transition product-main-image"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ get_image($product->thumbnail) }}"
                                                alt="{{ $product->getTranslation('name') }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                        </a>
                                    </div>
                                    <div class="text-center mt-2">
                                        <h3 class="fw-400 fs-13 text-truncate-2 lh-1-4 mb-1 h-35px">
                                            <a href="{{ route('customer.product', $product->slug) }}" class="text-reset hov-text-primary">{{ $product->getTranslation('name') }}</a>
                                        </h3>
                                        <div class="fw-700 fs-14 mb-1 mt-2">{{ single_price($product->unit_price) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endif

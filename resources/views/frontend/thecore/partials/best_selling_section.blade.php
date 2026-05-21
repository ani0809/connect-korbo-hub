@php
    $best_selling_products = get_best_selling_products(20);
@endphp
@if (get_setting('best_selling') == 1 && count($best_selling_products) > 0)
    <section class="p-3 rounded-2 best-salling-section h-100">
        <!-- Top Section -->
        <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between">
            <!-- Title -->
            <h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">
                <span class="">{{ translate('Best Selling') }}</span>
            </h3>
            <a type="button" class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" >
                <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                <span class="fs-12 mr-2 text">View All</span>
            </a>
        </div>
        <!-- Product Section -->
        <div class="cibato-carousel  arrow-inactive-transparent arrow-x-0 mt-2 carousel-arrow gutters-16"
            data-rows="1" data-items="4" data-xxl-items="4" data-xl-items="4" data-lg-items="4"
            data-md-items="3" data-sm-items="2" data-xs-items="1" data-arrows="false" data-dots="false" data-autoplay="true" data-infinite="true">
        
            @foreach ($best_selling_products as $key => $product)
                <div class="carousel-box mt-3 mb-1 px-1">
                    @include('frontend.partials.unified_product_card', ['product' => $product, 'card_padding_class' => 'px-0'])
                </div>
            @endforeach
        </div>

        
    </section>
@endif
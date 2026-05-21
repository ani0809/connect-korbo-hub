

@if (count($todays_deal_products) > 0)
    <!-- Top Section -->
<div class="border border-2 border-dark rounded-2 h-100">
    <div class="d-flex m-3 align-items-baseline justify-content-between">
            <!-- Title -->
            <h3 class="fs-14 fs-md-16 fw-500 mb-2 mb-sm-0">
                <span class="">{{ translate('Todays Deal') }}</span>
            </h3>
            <!-- Links -->
            <a type="button" class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" href="{{ route('todays-deal') }}">
                <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                <span class="fs-12 mr-2 text">View All</span>
            </a>
        </div>  
        
        <div class="cibato-carousel  arrow-inactive-transparent arrow-x-0 mt-2 carousel-arrow"
                data-rows="1" data-items="1" data-xxl-items="1" data-xl-items="1" data-lg-items="1"
                data-md-items="1" data-sm-items="1" data-xs-items="1" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true">
            
            @foreach ($todays_deal_products as $key => $product)
                <div class="carousel-box mt-3 mb-1">
                    @include('frontend.partials.unified_product_card', ['product' => $product])
                </div>
            @endforeach
        </div>
</div>
    

@endif

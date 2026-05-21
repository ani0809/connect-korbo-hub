 <!-- Featured Products -->

@if (count(get_featured_products()) > 0)
<section class="mb-2 mb-md-2 mt-2 mt-xl-4">
    <div class="container py-3 rounded-75" style="background: {{  get_setting('featured_section_bg_color') != null ?  get_setting('featured_section_bg_color') : '#ffffff' }}">
        <!-- Top Section -->
        <div class="d-flex mb-1 align-items-baseline justify-content-between">
            <!-- Title -->
            <h3 class="fs-16 fw-700 mb-2 mb-sm-0">
                <span class="">{{ translate('Featured Products') }}</span>
            </h3>
            <!-- Links -->
            <a type="button" class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" href="{{route('featured-products')}}">
                <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                <span class="fs-12 mr-2 text">View All</span>
            </a>
        </div>
        
        <div class="cibato-carousel  arrow-inactive-transparent arrow-x-0 mt-2 carousel-arrow"
            data-rows="1" data-items="3" data-xxl-items="3" data-xl-items="3" data-lg-items="3"
            data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows="false" data-dots="false" data-autoplay="false" data-infinite="true">
        
            @foreach (get_featured_products() as $key => $product)
                <div class="carousel-box mt-1 mb-1">
                    @include('frontend.partials.unified_product_card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
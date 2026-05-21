<section class="pt-4 pt-lg-5 pb-4">
    <div class="container">
        <div class="d-sm-flex">
            @php $best_selling_products = get_best_selling_products(20); @endphp
            @if (count($best_selling_products) > 0)
                <div class="px-0 px-sm-4 w-100 overflow-hidden rounded-75 best-salling-section pt-32px pb-26px mb-4 mb-sm-0" style="background-color: {{ get_setting('best_selling_section_bg_color', '#E7EFEC') }}">
                    <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between px-3 px-md-2">
                        <h3 class="fs-16 fw-600 mb-2 mb-sm-0">{{ translate('Best Selling') }}</h3>
                        <a class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" href="{{ route('best-selling') }}">
                            <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                            <span class="fs-12 mr-2 text">{{ translate('View All') }}</span>
                        </a>
                    </div>
                    <div class="cibato-carousel arrow-x-0 arrow-inactive-none gutters-16" data-items="4" data-xxl-items="4" data-xl-items="4" data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows="false" data-dots="false" data-autoplay="true" data-infinite="true">
                        @foreach ($best_selling_products as $product)
                            <div class="px-1">
                                @include('frontend.partials.unified_product_card', ['product' => $product, 'card_padding_class' => 'px-0'])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @php $todays_deal_products = get_todays_deal_products(20); @endphp
            @if (count($todays_deal_products) > 0)
                <div class="px-0 mt-sm-0 ml-sm-4 w-100 w-md-50 w-lg-35 overflow-hidden border border-2 border-dark rounded-75 todays-deal pt-32px pb-26px" style="background-color: {{ get_setting('todays_deal_bg_color', '#ffffff') }}">
                    <div class="d-flex mx-3 mb-3 align-items-baseline justify-content-between">
                        <h3 class="fs-16 fw-600 mb-2 mb-sm-0">{{ translate('Todays Deal') }}</h3>
                        <a class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center" href="{{ route('todays-deal') }}">
                            <span><i class="las la-angle-right fs-20 fw-600"></i></span>
                            <span class="fs-12 mr-2 text">View All</span>
                        </a>
                    </div>
                    <div class="cibato-carousel arrow-x-0 arrow-inactive-none" data-items="1" data-xxl-items="1" data-xl-items="1" data-lg-items="1" data-md-items="1" data-sm-items="1" data-xs-items="1" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true">
                        @foreach ($todays_deal_products as $product)
                            <div class="px-3">
                                @include('frontend.partials.unified_product_card', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<div class="bg-white border">
    <div class="p-3 p-sm-4">
        <h3 class="fs-16 fw-700 mb-0">
            <span class="mr-4">{{ translate('Frequently Bought Products') }}</span>
        </h3>
    </div>
    <div class="px-4">
        <div class="cibato-carousel gutters-5 half-outside-arrow" data-items="5" data-xl-items="3"
            data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2"
            data-arrows='true' data-infinite='true'>
            @foreach (get_frequently_bought_products($detailedProduct) as $key => $related_product)
                <div class="carousel-box">
                    @include('frontend.partials.unified_product_card', ['product' => $related_product])
                </div>
            @endforeach
        </div>
    </div>
</div>
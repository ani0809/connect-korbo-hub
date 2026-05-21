@foreach ($newest_products as $index => $new_product)
    <div class="col d-flex product-card">
        <div class="carousel-box has-transition rounded-2">
            @include('frontend.'.get_setting('homepage_select').'.partials.home_product_box', ['product' => $new_product, 'card_padding_class' => 'px-0'])
        </div>
    </div>
@endforeach

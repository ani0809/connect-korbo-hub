@php
    $category = null;
    if (!empty($category_id)) {
        $category = \App\Models\Category::with('coverImage')->find($category_id);
    }
@endphp

@if ($category)
    @php
        $category_name = $category->getTranslation('name');
        $display_title = !empty($custom_title ?? '') ? $custom_title : $category_name;
    @endphp
    <section class="mb-2 mb-md-3 mt-2 mt-xl-4" id="home-category-{{ $instance_id }}">
        <div class="container bg-white pt-3 pt-md-4 pb-1 pb-md-2 rounded-75 border">
            <div class="d-flex mb-3 align-items-center justify-content-between">
                <h3 class="fs-18 fw-700 mb-0">
                    <span class="text-dark">{{ $display_title }}</span>
                </h3>
                <div class="d-flex align-items-center">
                    <a href="{{ route('products.category', $category->slug) }}" class="text-primary fs-13 fw-600 mr-3 hov-text-dark">
                        {{ translate('View All') }}
                    </a>
                    <div class="d-flex">
                        <button type="button" aria-label="Previous" class="btn btn-sm rounded-circle mr-2 prev-custom-control slide-arrow link-disable arrow-prev d-flex align-items-center justify-content-center"
                            onclick="clickToSlide('slick-prev','home-category-{{ $instance_id }}')"
                            style="width:34px;height:34px;background:#fff;border:1px solid #cfd6e4;color:#1f2533;line-height:1;">
                            <i class="las la-angle-left fs-16" style="display:inline-block;color:#1f2533;"></i>
                            <span style="display:none;">&larr;</span>
                        </button>
                        <button type="button" aria-label="Next" class="btn btn-sm rounded-circle next-custom-control slide-arrow arrow-next d-flex align-items-center justify-content-center"
                            onclick="clickToSlide('slick-next','home-category-{{ $instance_id }}')"
                            style="width:34px;height:34px;background:#fff;border:1px solid #cfd6e4;color:#1f2533;line-height:1;">
                            <i class="las la-angle-right fs-16" style="display:inline-block;color:#1f2533;"></i>
                            <span style="display:none;">&rarr;</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="cibato-carousel arrow-x-0 arrow-inactive-none home-category home-category-carousel-tight h-100"
                data-items="5" data-xxl-items="5" data-xl-items="5"
                data-lg-items="3" data-md-items="2" data-sm-items="2"
                data-xs-items="2" data-arrows="false" data-infinite="true" data-autoplay="true">
                @foreach (get_cached_products($category->id) as $product)
                    <div class="carousel-box px-1 h-100">
                        <div class="h-100 border rounded-2 overflow-hidden hov-shadow-sm has-transition">
                            @include('frontend.'.get_setting('homepage_select').'.partials.product_box_1', [
                                'product' => $product,
                                'card_padding_class' => 'px-0',
                                'image_aspect_ratio' => '1 / 1.2',
                                'image_fill_cover' => true,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
            <style>
                #home-category-{{ $instance_id }} .home-category-carousel-tight .slick-slide { padding-left: 2px; padding-right: 2px; }
                #home-category-{{ $instance_id }} .home-category-carousel-tight .slick-list { margin-left: -2px; margin-right: -2px; }
            </style>
        </div>
    </section>
@endif


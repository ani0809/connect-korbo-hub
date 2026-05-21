@if (get_setting('home_categories') != null)
@php
    $home_categories = json_decode(get_setting('home_categories'));
    $categories = get_category($home_categories);
@endphp
@foreach ($categories as $category_key => $category)
    @php
        $category_name = $category->getTranslation('name');
    @endphp
    <section class="mb-2 mb-md-3 mt-2 mt-xl-4">
        <div class="container bg-white pt-3 pt-md-4 pb-3 pb-md-4 rounded-75 border">
            <!-- Section Header -->
            <div class="d-flex mb-3 align-items-center justify-content-between px-2">
                <h3 class="fs-18 fw-700 mb-0">
                    <span class="text-dark">{{ $category_name }}</span>
                </h3>
                <div class="d-flex align-items-center">
                    <a href="{{ route('products.category', $category->slug) }}" class="text-primary fs-13 fw-600 mr-3 hov-text-dark">
                        {{ translate('View All') }}
                    </a>
                    <div class="d-flex">
                        <button class="btn btn-sm btn-soft-dark rounded-circle mr-2 prev-custom-control">
                            <i class="las la-angle-left"></i>
                        </button>
                        <button class="btn btn-sm btn-soft-dark rounded-circle next-custom-control">
                            <i class="las la-angle-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row gutters-5">
                <!-- Side Banner -->
                <div class="col-12 col-md-5 col-lg-3">
                    <div class="h-200px h-md-350px mb-3 mb-md-0">
                        <a href="{{ route('products.category', $category->slug) }}" class="d-block h-100 w-100 overflow-hidden rounded-2 home-category-banner hov-scale-img">
                            <img src="{{ isset($category->coverImage->file_name) ? my_asset($category->coverImage->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                alt="{{ $category_name }}"
                                class="img-fit h-100 has-transition"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </a>
                    </div>
                </div>

                <!-- Product Slider -->
                <div class="col-12 col-md-7 col-lg-9">
                    <div class="cibato-carousel arrow-x-0 arrow-inactive-none home-category h-100"
                        data-items="5" data-xxl-items="5" data-xl-items="4"
                        data-lg-items="3" data-md-items="2" data-sm-items="2"
                        data-xs-items="2" data-arrows="false" data-infinite="true" data-autoplay="true">

                        @foreach (get_cached_products($category->id) as $product_key => $product)
                            <div class="carousel-box px-2 h-100">
                                <div class="h-100 border rounded-2 overflow-hidden hov-shadow-sm has-transition">
                                    @include('frontend.'.get_setting('homepage_select').'.partials.product_box_1', ['product' => $product])
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </section>
@endforeach
@endif

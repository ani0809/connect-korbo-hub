@if (count($featured_categories) > 0)
<div class="pt-32px" style="background: #ffffffff;">
    <div class="container">
        <div class="featured-categories rounded-75" style="background: {{ get_setting('featured_category_section_bg_color', '#ffffff') }} ; @if(get_setting('featured_category_section_outline') == 1) border: 2px solid {{ get_setting('featured_category_section_outline_color', '#000') }}; @endif">
            <div class="row pt-32px pb-26px">
                <div class="col-sm-6 col-md-4 col-lg-3 col-12 mb-3 mb-sm-0">
                    <div class="px-3 px-md-3">
                        <p class="fs-16 fw-700 font-weight-bold mb-1 mb-sm-3">{{ translate('Featured Categories') }}</p>
                        <p class="fs-13 fs-lg-14 fw-400 text-truncate-2" title="{{ translate('Categories catching eyes & winning hearts across our marketplace') }}">{{ translate('Categories catching eyes & winning hearts across our marketplace') }}</p>
                        <a class="btn fs-10 fs-md-16 custom-hov-btn py-2" href="{{ route('categories.all') }}" style="background: {{ get_setting('featured_category_btn_color', '#F94C10') }}; color: {{ get_setting('featured_category_section_btn_text_color', '#f5f5f5') }};">
                            <span class="d-inline">{{ translate('All Categories') }}</span>
                        </a>
                    </div>
                </div>
                <div class="col-sm-6 col-md-8 col-lg-9 col-12">
                    <div class="cibato-carousel arrow-inactive-transparent arrow-x-0 carousel-arrow"
                        data-rows="1" data-items="6" data-xxl-items="6" data-xl-items="5" data-lg-items="4"
                        data-md-items="3" data-sm-items="3" data-xs-items="3" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true">
                        @foreach ($featured_categories as $category)
                            @php $category_name = $category->getTranslation('name'); @endphp
                            <div class="carousel-box">
                                <div class="img w-60px h-60px w-sm-70px h-sm-70px h-md-100px w-md-100px h-lg-120px w-lg-120px rounded overflow-hidden mx-auto hov-scale-img">
                                    <a href="{{ route('products.category', $category->slug) }}">
                                        <img class="lazyload img-fit m-auto has-transition"
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ isset($category->cover_image) ? uploaded_asset($category->cover_image) : static_asset('assets/img/placeholder.jpg') }}"
                                            alt="{{ $category_name }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    </a>
                                </div>
                                <div class="fs-11 mr-1 mt-3 text-center mt-2" title="{{ $category_name }}">
                                    <a class="fw-400 text-reset hov-text-primary" href="{{ route('products.category', $category->slug) }}">
                                        {{ strlen($category_name) > 18 ? substr($category_name, 0, 18).'...' : $category_name }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

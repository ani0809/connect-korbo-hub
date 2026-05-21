@if(count($todays_deal_products) > 0)
    <section  class="mb-2 mb-md-3 mt-2 mt-md-3">
        <div class="container">
            @php
                $lang = get_system_language()->code;
                $todays_deal_banner = get_setting('todays_deal_banner', null, $lang);
                $todays_deal_banner_small = get_setting('todays_deal_banner_small', null, $lang);
            @endphp
            <div class="row no-gutters">
                <!-- Banner -->
                @if ($todays_deal_banner != null || $todays_deal_banner_small != null)
                    <div class="col-xl-5">
                        <div class="overflow-hidden h-100 d-none d-md-block">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" 
                                data-src="{{ uploaded_asset($todays_deal_banner) }}" 
                                alt="{{ env('APP_NAME') }} promo" class="lazyload img-fit h-100 has-transition" 
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        </div>
                        <div class="overflow-hidden h-100 d-md-none">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" 
                                data-src="{{ $todays_deal_banner_small != null ? uploaded_asset($todays_deal_banner_small) : uploaded_asset($todays_deal_banner) }}" 
                                alt="{{ env('APP_NAME') }} promo" class="lazyload img-fit h-100 has-transition" 
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        </div>
                    </div>
                @endif
                <!-- Products -->
                @php
                    $todays_deal_banner_text_color =  ((get_setting('todays_deal_banner_text_color') == 'light') ||  (get_setting('todays_deal_banner_text_color') == null)) ? 'text-white' : 'text-dark';
                    $col_val = $todays_deal_banner != null ? 'col-xl-7' : 'col-xl-12';
                    $xxl_items = $todays_deal_banner != null ? 5 : 7;
                    $xl_items = $todays_deal_banner != null ? 4 : 6;
                @endphp
                <div class="{{ $col_val }}" style="background-color: {{ get_setting('todays_deal_bg_color', '#3d4666') }}">
                    <div class="d-flex flex-wrap align-items-baseline justify-content-between px-4 px-xl-5 pt-4">
                        <h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">{{ translate("Today’s Deal") }}</h3>
                        <a href="{{ route('todays-deal') }}" class="fs-12 fw-700 {{ $todays_deal_banner_text_color }} has-transition hov-text-secondary-base">{{ translate('View All') }}</a>
                    </div>
                    <div class="c-scrollbar-light overflow-hidden px-4 px-md-5 pb-3 pt-3 pt-md-3 pb-md-5">
                        <div class="h-100 d-flex flex-column justify-content-center">
                            <div class="todays-deal cibato-carousel" data-items="{{ $xxl_items }}" data-xxl-items="{{ $xxl_items }}" data-xl-items="{{ $xxl_items }}" data-lg-items="5" data-md-items="4" data-sm-items="3" data-xs-items="2" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true">
                                @foreach ($todays_deal_products as $key => $product)
                                    <div class="carousel-box h-100 px-3 px-lg-0">
                                        @include('frontend.partials.unified_product_card', ['product' => $product])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endif
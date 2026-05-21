@php $lang = get_system_language()->code; $homeBanner1Images = get_setting('home_banner1_images', null, $lang); @endphp
@if ($homeBanner1Images != null)
<div style="padding-top:20px;padding-bottom:20px;">
    <div class="container">
        @php
            $banner_1_imags = json_decode($homeBanner1Images);
            $data_md = count($banner_1_imags) >= 2 ? 2 : 1;
            $home_banner1_links = get_setting('home_banner1_links', null, $lang);
        @endphp
        <div class="w-100">
            <div class="cibato-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15 home-banner-1"
                data-items="{{ count($banner_1_imags) }}" data-xxl-items="{{ count($banner_1_imags) }}"
                data-xl-items="{{ count($banner_1_imags) }}" data-lg-items="{{ $data_md }}"
                data-md-items="2.5" data-sm-items="2.5" data-xs-items="1.5" data-arrows="false"
                data-dots="false" data-autoplay="true" data-infinite="true">
                @foreach ($banner_1_imags as $key => $value)
                    <div class="carousel-box overflow-hidden hov-scale-img">
                        <a href="{{ isset(json_decode($home_banner1_links, true)[$key]) ? json_decode($home_banner1_links, true)[$key] : '' }}"
                            class="d-block text-reset overflow-hidden rounded-75 h-100">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                                class="lazyload img-fit h-100 has-transition"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

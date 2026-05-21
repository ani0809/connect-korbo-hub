@php
    $lang = get_system_language()->code;
    $homeBanner2Images = get_setting('home_banner2_images', null, $lang);
    $homeBanner2SmallImages = get_setting('home_banner2_sm_images', null, $lang);
@endphp
@if ($homeBanner2Images != null)
<div style="padding-top:20px;padding-bottom:20px;">
    <div class="container">
        @php
            $banner_2_imags = json_decode($homeBanner2Images, true) ?? [];
            $banner_2_small_imags = json_decode($homeBanner2SmallImages, true) ?? [];
            $data_md = count($banner_2_imags) >= 2 ? 2 : 1;
            $data_small_md = count($banner_2_small_imags) >= 2 ? 2 : 1;
            $home_banner2_links = get_setting('home_banner2_links', null, $lang);
        @endphp
        <div class="d-none d-md-block cibato-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
            data-items="{{ count($banner_2_imags) }}" data-xxl-items="{{ count($banner_2_imags) }}"
            data-xl-items="{{ count($banner_2_imags) }}" data-lg-items="{{ $data_md }}"
            data-md-items="{{ $data_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true" data-dots="false">
            @foreach ($banner_2_imags as $key => $value)
                <div class="carousel-box overflow-hidden hov-scale-img">
                    <a href="{{ isset(json_decode($home_banner2_links, true)[$key]) ? json_decode($home_banner2_links, true)[$key] : '' }}"
                        class="d-block text-reset overflow-hidden rounded-75">
                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                            class="img-fluid lazyload w-100 has-transition" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                    </a>
                </div>
            @endforeach
        </div>
        <div class="d-md-none cibato-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
            data-items="{{ count($banner_2_imags) }}" data-xxl-items="{{ count($banner_2_imags) }}"
            data-xl-items="{{ count($banner_2_imags) }}" data-lg-items="{{ $data_small_md }}"
            data-md-items="{{ $data_small_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true" data-dots="false">
            @foreach ($banner_2_small_imags as $key => $value)
                <div class="carousel-box overflow-hidden hov-scale-img">
                    <a href="{{ isset(json_decode($home_banner2_links, true)[$key]) ? json_decode($home_banner2_links, true)[$key] : '' }}"
                        class="d-block text-reset overflow-hidden rounded-75">
                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} promo"
                            class="img-fluid lazyload w-100 has-transition" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

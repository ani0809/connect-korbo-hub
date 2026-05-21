@php $lang = get_system_language()->code; @endphp
<div class="pt-32px pb-26px" style="background: {{ get_setting('hero_bg_color', '#f5f5f5') }}">
    <div class="container">
        @if (get_setting('home_slider_images', null, $lang) != null)
            <div class="cibato-carousel dots-inside-bottom thecore-hero-slider" data-autoplay="true" data-infinite="true">
                @php
                    $decoded_slider_images = json_decode(get_setting('home_slider_images', null, $lang), true);
                    $sliders = get_slider_images($decoded_slider_images);
                    $home_slider_links = get_setting('home_slider_links', null, $lang);
                @endphp
                @foreach ($sliders as $key => $slider)
                    <div class="carousel-box">
                        <a href="{{ isset(json_decode($home_slider_links, true)[$key]) ? json_decode($home_slider_links, true)[$key] : '' }}">
                            <div class="thecore-square-box overflow-hidden h-250px h-md-350px h-lg-450px h-xl-500px h-xxl-600px">
                                <img class="img-fluid rounded-75 border border-light h-100 w-100"
                                    src="{{ $slider ? my_asset($slider->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                    alt="{{ env('APP_NAME') }} promo"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

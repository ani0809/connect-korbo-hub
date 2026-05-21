<!-- Last Viewed Products  -->
@if(get_setting('last_viewed_product_activation') == 1 && Auth::check() && auth()->user()->user_type == 'customer')
<div class="border-top" id="section_last_viewed_products" style="background-color: #fcfcfc;">
    @php
    $lastViewedProducts = getLastViewedProducts();
    @endphp
    @if (count($lastViewedProducts) > 0)
        <section class="my-2 my-md-3">
            <div class="container">
                <!-- Top Section -->
                <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between">
                    <!-- Title -->
                    <h3 class="fs-16 fw-700 mb-2 mb-sm-0">
                        <span class="">{{ translate('Last Viewed Products') }}</span>
                    </h3>
                    <!-- Links -->
                    <div class="d-flex">
                        <a href="javascript:void(0)" type="button" class="arrow-prev slide-arrow link-disable text-secondary mr-2" aria-label="{{ translate('Previous products') }}" onclick="clickToSlide('slick-prev','section_last_viewed_products')"><i class="las la-angle-left fs-20 fw-600" aria-hidden="true"></i></a>
                        <a href="javascript:void(0)" type="button" class="arrow-next slide-arrow text-secondary ml-2" aria-label="{{ translate('Next products') }}" onclick="clickToSlide('slick-next','section_last_viewed_products')"><i class="las la-angle-right fs-20 fw-600" aria-hidden="true"></i></a>
                    </div>
                </div>
                <!-- Product Section -->
                <div class="px-sm-3">
                    <div class="cibato-carousel slick-left sm-gutters-16 arrow-none" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true' data-infinite='false'>
                        @foreach ($lastViewedProducts as $key => $lastViewedProduct)
                            <div class="carousel-box px-3 position-relative has-transition hov-animate-outline border-right border-top border-bottom @if($key == 0) border-left @endif">
                                @include('frontend.'.get_setting('homepage_select').'.partials.last_view_product_box_1',['product' => $lastViewedProduct->product])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
@endif

<!-- footer Description -->
@if (get_setting('footer_title') != null || get_setting('footer_description') != null)
    <section class="bg-light border-top border-bottom mt-auto">
        <div class="container py-32px">
            <h1 class="fs-18 fw-700 text-gray-dark mb-3">{{ get_setting('footer_title', null, $system_language->code) }}</h1>
            @php
                $fullDescription = nl2br(get_setting('footer_description', null, $system_language->code));
            @endphp
            
            <div class="footer-desc-container">
                <p class="footer-text-control fs-13 text-gray-dark text-justify mb-0">
                        {!! $fullDescription !!}
                </p>
                <div class="text-control-btn mt-2 d-xl-none">
                    
                    <a class="text-primary cursor-pointer toggle-btn" id="toggle-btn" >
                        Read More
                    </a>
                </div>
            </div>
        </div>
    </section>
@endif

<!-- footer -->
<style>
    .revive-footer-area {
        background: #2b2b2b;
        padding: 42px 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .revive-footer-panel {
        width: min(1400px, calc(100% - 44px)) !important;
        max-width: calc(100% - 44px) !important;
        margin: 0 auto;
        background: #050505;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        overflow: hidden;
        min-height: clamp(300px, 32vw, 360px);
        display: flex;
        flex-direction: column;
    }
    .revive-footer-main {
        padding: 14px 28px 12px;
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .revive-footer-main > .row {
        width: 100%;
        max-width: 1320px;
        margin: 0 auto;
        align-items: flex-start;
        justify-content: space-between;
        transform: translateY(-2px);
    }
    .revive-footer-bottom {
        border-top: 0;
        background: #050505;
        padding: 6px 28px 14px;
    }
    .revive-title {
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .revive-text,
    .revive-link,
    .revive-copy {
        color: #ffffff !important;
        font-size: 16px;
        line-height: 1.6;
    }
    .revive-link:hover {
        color: var(--primary) !important;
    }
    .revive-social a {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #0f0f0f;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.16);
        color: var(--primary) !important;
        transition: all .22s ease;
    }
    .revive-social i {
        font-size: 14px;
    }
    .revive-social a:hover {
        background: var(--primary);
        color: #ffffff !important;
        box-shadow: 0 6px 14px rgba(0,0,0,.35), inset 0 0 0 1px var(--primary);
        transform: translateY(-2px) scale(1.05);
    }
    .revive-social a:focus-visible,
    .revive-link:focus-visible,
    .revive-mobile-toggle:focus-visible,
    .cibato-mobile-bottom-nav a:focus-visible,
    .slide-arrow:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
        box-shadow: 0 0 0 3px rgba(10, 168, 223, 0.2);
    }
    .revive-logo {
        max-height: 62px;
    }
    .revive-copy {
        color: #ffffff !important;
        font-size: 16px;
    }
    .revive-footer-main .row > div {
        margin-bottom: 0;
    }
    .revive-col-logo { flex: 0 0 33%; max-width: 33%; }
    .revive-col-contact { flex: 0 0 22%; max-width: 22%; }
    .revive-col-categories { flex: 0 0 16%; max-width: 16%; }
    .revive-col-links { flex: 0 0 20%; max-width: 20%; }
    .revive-col-logo,
    .revive-col-contact,
    .revive-col-categories,
    .revive-col-links {
        align-self: flex-start;
    }
    .revive-contact-list li {
        margin-bottom: 8px;
    }
    .revive-contact-list i {
        width: 16px;
        margin-right: 8px;
        color: var(--primary);
        text-align: center;
    }
    .revive-links li {
        margin-bottom: 7px;
    }
    .revive-footer-main .col-lg-4 .revive-text,
    .revive-footer-main .col-lg-4 .revive-text * {
        color: #ffffff !important;
        opacity: 1 !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .revive-footer-main .col-lg-4 .revive-text [style] {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .revive-footer-main .col-lg-4 p,
    .revive-footer-main .col-lg-4 p * {
        color: #ffffff !important;
        opacity: 1 !important;
        -webkit-text-fill-color: #ffffff !important;
        text-shadow: none !important;
    }
    .revive-about-text {
        color: #ffffff !important;
        opacity: 1 !important;
        -webkit-text-fill-color: #ffffff !important;
        filter: none !important;
    }
    .revive-footer-bottom .row {
        margin-top: -2px;
    }
    .revive-footer-bottom img {
        margin-top: -2px;
    }
    .revive-mobile-toggle {
        display: none;
    }
    @media (max-width: 1199.98px) {
        .revive-col-logo,
        .revive-col-contact,
        .revive-col-categories,
        .revive-col-links {
            flex: 0 0 100%;
            max-width: 100%;
        }
        .revive-footer-area {
            padding-top: 24px;
            padding-bottom: 22px;
        }
        .revive-footer-panel {
            width: calc(100% - 20px);
            min-height: clamp(300px, 70vw, 360px);
        }
        .revive-footer-main {
            padding: 22px 16px 12px;
            display: block;
        }
        .revive-footer-main > .row {
            transform: translateY(0);
        }
        .revive-footer-main .row > div {
            margin-bottom: 16px;
        }
        .revive-footer-bottom {
            padding: 10px 16px 12px;
        }
        .revive-mobile-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: transparent;
            border: 0;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            padding: 6px 0;
            margin-bottom: 6px;
        }
        .revive-mobile-toggle:focus {
            outline: none;
        }
        .revive-mobile-toggle .toggle-icon {
            font-size: 16px;
            color: var(--primary);
            transition: transform .2s ease;
        }
        .revive-mobile-toggle[aria-expanded="true"] .toggle-icon {
            transform: rotate(180deg);
        }
        .revive-mobile-collapse {
            padding-bottom: 4px;
        }
    }
</style>

<div class="revive-footer-area">
    <div class="revive-footer-panel">
        <div class="revive-footer-main">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0 revive-col-logo">
                    <a href="{{ route('home') }}" class="d-inline-block mb-3">
                @if(get_setting('footer_logo') != null)
                            <img class="lazyload revive-logo" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset(get_setting('footer_logo')) }}" alt="{{ env('APP_NAME') }}">
                @else
                            <img class="lazyload revive-logo" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}">
                @endif
            </a>
                    @php
                        $footerAboutText = trim(strip_tags((string) get_setting('about_us_description', null, App::getLocale())));
                    @endphp
                    <p class="revive-text revive-about-text mb-3">{!! nl2br(e($footerAboutText)) !!}</p>

                @if ( get_setting('show_social_links') )
                        <ul class="list-inline revive-social mb-0">
                        @if (!empty(get_setting('facebook_link')))
                                <li class="list-inline-item mr-2"><a href="{{ get_setting('facebook_link') }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('Facebook') }}" class="facebook"><i class="lab la-facebook-f" aria-hidden="true"></i></a></li>
                        @endif
                        @if (!empty(get_setting('twitter_link')))
                                <li class="list-inline-item mr-2"><a href="{{ get_setting('twitter_link') }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('Twitter') }}" class="x-twitter"><i class="lab la-twitter" aria-hidden="true"></i></a></li>
                        @endif
                        @if (!empty(get_setting('instagram_link')))
                                <li class="list-inline-item mr-2"><a href="{{ get_setting('instagram_link') }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('Instagram') }}" class="instagram"><i class="lab la-instagram" aria-hidden="true"></i></a></li>
                        @endif
                        @if (!empty(get_setting('youtube_link')))
                                <li class="list-inline-item mr-2"><a href="{{ get_setting('youtube_link') }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('YouTube') }}" class="youtube"><i class="lab la-youtube" aria-hidden="true"></i></a></li>
                            @endif
                            @if (!empty(get_setting('linkedin_link')))
                                <li class="list-inline-item mr-2"><a href="{{ get_setting('linkedin_link') }}" target="_blank" rel="noopener noreferrer" aria-label="{{ translate('LinkedIn') }}" class="linkedin"><i class="lab la-linkedin-in" aria-hidden="true"></i></a></li>
                            @endif
                        </ul>
                    @endif
    </div>

                <div class="col-lg-3 mb-4 mb-lg-0 revive-col-contact">
                    <h4 class="revive-title d-none d-xl-block">{{ translate('Contact Us') }}</h4>
                    <button class="revive-mobile-toggle d-xl-none" type="button" data-toggle="collapse" data-target="#reviveFooterContact" aria-expanded="true" aria-controls="reviveFooterContact">
                        <span>{{ translate('Contact Us') }}</span>
                        <i class="las la-angle-down toggle-icon"></i>
                    </button>
                    <div id="reviveFooterContact" class="collapse show d-xl-block revive-mobile-collapse">
                        <ul class="list-unstyled mb-0 revive-contact-list">
                            <li class="revive-text">
                                <i class="las la-phone-alt"></i><a href="tel:{{ get_setting('contact_phone') }}" class="revive-link">{{ get_setting('contact_phone') }}</a>
                            </li>
                            <li class="revive-text">
                                <i class="las la-envelope"></i><a href="mailto:{{ get_setting('contact_email') }}" class="revive-link">{{ get_setting('contact_email') }}</a>
                            </li>
                            <li class="revive-text">
                                <i class="las la-map-marker-alt"></i>{{ get_setting('contact_address',null,App::getLocale()) }}
                            </li>
                    </ul>
                    </div>
                </div>

                <div class="col-lg-2 mb-4 mb-lg-0 revive-col-categories">
                    <h4 class="revive-title d-none d-xl-block">{{ translate('Categories') }}</h4>
                    <button class="revive-mobile-toggle d-xl-none collapsed" type="button" data-toggle="collapse" data-target="#reviveFooterCategories" aria-expanded="false" aria-controls="reviveFooterCategories">
                        <span>{{ translate('Categories') }}</span>
                        <i class="las la-angle-down toggle-icon"></i>
                    </button>
                    <div id="reviveFooterCategories" class="collapse d-xl-block revive-mobile-collapse">
                        <ul class="list-unstyled mb-0 revive-links">
                            @php
                                $labels = get_setting('widget_one_labels',null,App::getLocale()) ? json_decode(get_setting('widget_one_labels',null,App::getLocale()), true) : [];
                                $links = get_setting('widget_one_links') ? json_decode(get_setting('widget_one_links'), true) : [];
                            @endphp
                            @foreach(array_slice($labels,0,5) as $key => $value)
                                <li class="mb-2"><a href="{{ $links[$key] ?? '#' }}" class="revive-link">{{ $value }}</a></li>
                            @endforeach
                        </ul>
            </div>
        </div>

                <div class="col-lg-3 revive-col-links">
                    <h4 class="revive-title d-none d-xl-block">{{ translate('Useful Links') }}</h4>
                    <button class="revive-mobile-toggle d-xl-none collapsed" type="button" data-toggle="collapse" data-target="#reviveFooterUsefulLinks" aria-expanded="false" aria-controls="reviveFooterUsefulLinks">
                        <span>{{ translate('Useful Links') }}</span>
                        <i class="las la-angle-down toggle-icon"></i>
                    </button>
                    <div id="reviveFooterUsefulLinks" class="collapse d-xl-block revive-mobile-collapse">
                        <ul class="list-unstyled mb-0 revive-links">
                            <li class="mb-2"><a href="{{ route('home') }}" class="revive-link">{{ translate('About Us') }}</a></li>
                            <li class="mb-2"><a href="{{ route('contact') }}" class="revive-link">{{ translate('Contact Us') }}</a></li>
                            <li class="mb-2"><a href="{{ route('terms') }}" class="revive-link">{{ translate('Terms & Conditions') }}</a></li>
                            <li class="mb-2"><a href="{{ route('privacypolicy') }}" class="revive-link">{{ translate('Privacy Policy') }}</a></li>
                            <li class="mb-2"><a href="{{ route('returnpolicy') }}" class="revive-link">{{ translate('Return Policy') }}</a></li>
                    </ul>
                </div>
                </div>
            </div>
        </div>

        <div class="revive-footer-bottom">
            <div class="row align-items-center">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="revive-copy text-center text-lg-left" current-verison="{{get_setting("current_version")}}">
                    {!! get_setting('frontend_copyright_text', null, App::getLocale()) !!}
                </div>
            </div>
                <div class="col-lg-6 mb-3 mb-lg-0 order-1 order-lg-2">
                <div class="text-center text-lg-right">
                    <ul class="list-inline mb-0">
                        @if ( get_setting('payment_method_images') !=  null )
                            @foreach (explode(',', get_setting('payment_method_images')) as $key => $value)
                                    <li class="list-inline-item mr-2">
                                        <img src="{{ uploaded_asset($value) }}" height="18" class="mw-100 h-auto" style="max-height: 18px" alt="{{ translate('payment_method') }}">
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Mobile bottom nav -->
<style>
    @media (max-width: 1199.98px) {
        .cibato-mobile-bottom-nav {
            left: 10px !important;
            right: 10px !important;
            width: auto !important;
            max-width: 520px;
            height: 68px !important;
            margin: 0 auto 10px !important;
            padding: 0 8px;
            border: 1px solid rgba(255, 255, 255, 0.55) !important;
            border-radius: 22px !important;
            background: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow: 0 10px 24px rgba(18, 22, 33, 0.14) !important;
            z-index: 1032;
        }

        /* Extend main footer dark bg up to the floating nav */
        footer.bg-black {
            padding-bottom: var(--mobile-nav-clearance, 84px) !important;
            background: linear-gradient(90deg, #16192a 0%, #111425 55%, #16192a 100%) !important;
        }

        .cibato-mobile-bottom-nav::before,
        .cibato-mobile-bottom-nav::after {
            border: 0 !important;
            box-shadow: none !important;
        }
        .cibato-mobile-bottom-nav::after {
            content: none !important;
            display: none !important;
        }

        .cibato-mobile-bottom-nav .row {
            height: 100%;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .cibato-mobile-bottom-nav .col,
        .cibato-mobile-bottom-nav .col-auto {
            padding-left: 4px !important;
            padding-right: 4px !important;
        }

        .cibato-mobile-bottom-nav a {
            height: 58px;
            border-radius: 10px;
            display: flex !important;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            padding-top: 7px !important;
            padding-bottom: 6px !important;
            transition: background-color .2s ease, transform .2s ease;
        }

        .cibato-mobile-bottom-nav a:hover {
            background-color: rgba(52, 144, 243, 0.10);
            transform: translateY(-1px);
        }

        .cibato-mobile-bottom-nav a.svg-active {
            background-color: rgba(52, 144, 243, 0.14);
        }

        .cibato-mobile-bottom-nav a i {
            font-size: 18px;
            color: #8f96a3;
        }

        .cibato-mobile-bottom-nav a span {
            font-size: 11px !important;
            line-height: 1.1;
            color: #8f96a3 !important;
        }

        .cibato-mobile-bottom-nav a.svg-active i,
        .cibato-mobile-bottom-nav a.svg-active span {
            color: #3490f3 !important;
        }

        .cibato-mobile-bottom-nav .cart-count {
            font-weight: 700;
        }

        .cibato-mobile-bottom-nav .badge-dot {
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.95);
        }

    }
 </style>
<div class="cibato-mobile-bottom-nav d-xl-none fixed-bottom mx-auto mb-sm-2" style="background-color: rgb(255 255 255 / 90%)!important;">
    @php
        $count = count(get_user_cart());
    @endphp
    <div class="row align-items-center gutters-5">
        <!-- Home -->
        <div class="col">
            <a href="{{ route('home') }}" aria-label="{{ translate('Home') }}" class="text-secondary d-block text-center pb-2 pt-3 {{ areActiveRoutes(['home'],'svg-active')}}">
                <i class="las la-home" aria-hidden="true"></i>
                <span class="d-block mt-1 fs-10 fw-600 text-reset {{ areActiveRoutes(['home'],'text-primary')}}">{{ translate('Home') }}</span>
            </a>
        </div>

        <!-- Categories -->
        <div class="col">
            <a href="{{ route('categories.all') }}" aria-label="{{ translate('Categories') }}" class="text-secondary d-block text-center pb-2 pt-3 {{ areActiveRoutes(['categories.all'],'svg-active')}}">
                <i class="las la-th-large" aria-hidden="true"></i>
                <span class="d-block mt-1 fs-10 fw-600 text-reset {{ areActiveRoutes(['categories.all'],'text-primary')}}">{{ translate('Categories') }}</span>
            </a>
        </div>

            <!-- Cart -->
        <div class="col">
            <a href="{{ route('cart') }}" aria-label="{{ translate('Cart') }}" class="text-secondary d-block text-center pb-2 pt-3 {{ areActiveRoutes(['cart'],'svg-active')}}">
                <span class="d-inline-block position-relative">
                    <i class="las la-shopping-bag" aria-hidden="true"></i>
                        @if($count > 0)
                        <span class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right" style="right: -2px;top: 1px;"></span>
                        @endif
                    </span>
                    <span class="d-block mt-1 fs-10 fw-600 text-reset {{ areActiveRoutes(['cart'],'text-primary')}}">
                        {{ translate('Cart') }}
                    </span>
                </a>
            </div>

        <!-- Account -->
        <div class="col">
            @if (Auth::check())
                @if(isAdmin())
                    <a href="{{ route('admin.dashboard') }}" aria-label="{{ translate('My Account') }}" class="text-secondary d-block text-center pb-2 pt-3">
                        <i class="las la-user-circle" aria-hidden="true"></i>
                        <span class="d-block mt-1 fs-10 fw-600 text-reset">{{ translate('My Account') }}</span>
                    </a>
                @elseif(isSeller())
                    <a href="{{ route('dashboard') }}" aria-label="{{ translate('My Account') }}" class="text-secondary d-block text-center pb-2 pt-3">
                        <i class="las la-user-circle" aria-hidden="true"></i>
                        <span class="d-block mt-1 fs-10 fw-600 text-reset">{{ translate('My Account') }}</span>
                    </a>
                @else
                    <a href="javascript:void(0)" role="button" aria-label="{{ translate('Open My Account menu') }}" class="text-secondary d-block text-center pb-2 pt-3 mobile-side-nav-thumb" data-toggle="class-toggle" data-backdrop="static" data-target=".cibato-mobile-side-nav">
                        <i class="las la-user-circle" aria-hidden="true"></i>
                        <span class="d-block mt-1 fs-10 fw-600 text-reset">{{ translate('My Account') }}</span>
                    </a>
                @endif
            @else
                <a href="{{ route('user.login') }}" aria-label="{{ translate('My Account') }}" class="text-secondary d-block text-center pb-2 pt-3">
                    <i class="las la-user-circle" aria-hidden="true"></i>
                    <span class="d-block mt-1 fs-10 fw-600 text-reset">{{ translate('My Account') }}</span>
                </a>
            @endif
        </div>

    </div>
</div>

@if (Auth::check() && auth()->user()->user_type == 'customer')
    <!-- User Side nav -->
    <div class="cibato-mobile-side-nav collapse-sidebar-wrap sidebar-xl d-xl-none z-1035">
        <div class="overlay dark c-pointer overlay-fixed" data-toggle="class-toggle" data-backdrop="static" data-target=".cibato-mobile-side-nav" data-same=".mobile-side-nav-thumb"></div>
        <div class="collapse-sidebar bg-white">
            @include('frontend.inc.user_side_nav')
        </div>
    </div>
@endif


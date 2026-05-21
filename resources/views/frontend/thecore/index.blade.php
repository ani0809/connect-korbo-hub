@extends('frontend.layouts.app')


@section('content')
<style>
    .thecore-home-section {
        margin-top: 20px !important;
        margin-bottom: 20px !important;
    }
    .thecore-home-section > section,
    .thecore-home-section > div {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    .thecore-home-section:first-of-type {
        margin-top: 20px !important;
    }
    .thecore-home-section:last-of-type {
        margin-bottom: 20px !important;
    }
    @media (max-width: 767px) {
        #flash_deal .flash-deals-baner {
            height: 203px !important;
        }
    }
    .newest-load-more-btn {
        border: 1px solid var(--soft-primary);
        background: linear-gradient(135deg, var(--soft-primary) 0%, #ffffff 100%);
        color: var(--primary) !important;
        border-radius: 999px;
        min-width: 164px;
        padding: 12px 24px;
        font-weight: 700;
        letter-spacing: 0.2px;
        transition: all .25s ease;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.1);
    }
    .newest-load-more-btn:hover {
        transform: translateY(-1px);
        background: var(--primary);
        color: #fff !important;
        border-color: var(--primary);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.16);
    }
    .newest-load-more-btn:disabled {
        opacity: 0.75;
        transform: none;
        box-shadow: none;
    }
    @media (max-width: 767px) {
        .best-salling-section .cibato-card-box .size-180px,
        .best-salling-section .cibato-card-box .size-170px,
        .best-salling-section .cibato-card-box .size-190px {
            height: 145px !important;
        }
        .featured-categories .row > .col-sm-6.col-md-4.col-lg-3.col-12 {
            padding-left: 14px;
            padding-right: 14px;
        }
    }
</style>
<input type="hidden" id="selected_homepage" value="{{get_setting('homepage_select')}}">
@php $layoutItems = $layoutItems ?? get_thecore_homepage_layout(); @endphp
@include('frontend.thecore.partials.layout_renderer', ['layoutItems' => $layoutItems, 'featured_categories' => $featured_categories])

@endsection

@section('script')
<script>
    (function() {
        const routes = {
            featured_products: '{{ route('home.section.featured') }}',
            newest_products: '{{ route('home.section.newest_products') }}',
            home_categories: '{{ route('home.section.home_categories') }}',
            auction_products: '{{ route('home.section.auction_products') }}'
        };

        function loadAsyncSections() {
            $('[data-home-section-placeholder]').each(function() {
                const $placeholder = $(this);
                const sectionKey = $placeholder.data('home-section');
                const route = routes[sectionKey];
                if (!route) return;

                const payload = { _token: '{{ csrf_token() }}' };
                if (sectionKey === 'newest_products' || sectionKey === 'featured_products') {
                    payload.section_id = $placeholder.data('section-id');
                }
                if (sectionKey === 'newest_products') {
                    payload.custom_title = $placeholder.attr('data-custom-title') || '';
                }

                $.post(route, payload, function(data) {
                    $placeholder.html(data);
                    CIBATO.plugins.slickCarousel();
                    forceAutoplay($placeholder);
                    if (sectionKey === 'newest_products') {
                        toggleViewMoreButton($placeholder.data('section-id'));
                    }
                }).fail(function() {
                    $placeholder.html('<div class="text-center py-3 text-muted">{{ translate("Failed to load section") }}</div>');
                });
            });
        }

        function forceAutoplay($scope) {
            $scope.find('.cibato-carousel.slick-initialized').each(function() {
                const $slider = $(this);
                try {
                    $slider.slick('slickSetOption', 'autoplay', true, true);
                    $slider.slick('slickSetOption', 'autoplaySpeed', 2600, true);
                } catch (e) {
                    // keep silent, fallback to data attributes
                }
            });
        }

        function toggleViewMoreButton(sectionId) {
            const $section = $('#section_newest_' + sectionId);
            const $container = $('[data-view-more-container="' + sectionId + '"]');
            if ($.trim($section.html()).length > 0) {
                $container.removeClass('d-none').addClass('d-block');
            } else {
                $container.removeClass('d-block').addClass('d-none');
            }
        }

        $(document).on('click', '.view-more-btn', function() {
            const $button = $(this);
            const sectionId = $button.data('section-id');
            const currentPage = parseInt($button.data('page') || 1, 10) + 1;
            const originalText = $button.html();
            const targetSelector = '#newest-products-list-' + sectionId;

            $button.data('page', currentPage);
            $button.prop('disabled', true);
            $button.html('{{ translate("Loading...") }} <i class="las la-lg la-spinner la-spin"></i>');

            $.post('{{ route('home.section.newest_products') }}', {
                _token: '{{ csrf_token() }}',
                page: currentPage,
                section_id: sectionId
            }, function(data) {
                $button.prop('disabled', false);
                $button.html(originalText);
                if ($.trim(data) === '') {
                    $button.prop('disabled', true).text('{{ translate("No More Products") }}');
                    return;
                }
                $('#newest-products-list-' + sectionId).append(data);
                CIBATO.plugins.slickCarousel();
            }).fail(function() {
                $button.prop('disabled', false);
                $button.html('{{ translate("Error, Try Again") }}');
            });
        });

        $(document).ready(function() {
            loadAsyncSections();
        });
    })();
</script>
@endsection
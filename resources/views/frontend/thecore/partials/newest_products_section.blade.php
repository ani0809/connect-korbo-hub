@if (count($newest_products) > 0)
@php
    $newestSectionId = $section_id ?? 'default';
    $displayTitle = !empty(trim($custom_title ?? '')) ? $custom_title : translate('Newest Products');
@endphp
<section class="py-0" style="margin-top: 30px;">
    <div class="container">
        <h3 class="fs-18 fw-700 mb-3 text-dark">{{ $displayTitle }}</h3>
        <div class="row gutters-16 row-cols-xl-5 row-cols-lg-5 row-cols-md-4 row-cols-sm-3 row-cols-2 newest-products-list" id="newest-products-list-{{ $newestSectionId }}">
            @include('frontend.partials.newest_product_cards', ['newest_products' => $newest_products])
        </div>
    </div>
</section>
@endif

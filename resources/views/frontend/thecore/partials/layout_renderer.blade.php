@php
    $sectionPartialMap = [
        'hero_slider' => 'frontend.thecore.partials.dynamic_sections.hero_slider',
        'featured_categories' => 'frontend.thecore.partials.dynamic_sections.featured_categories',
        'featured_products' => 'frontend.thecore.partials.dynamic_sections.featured_products',
        'best_selling_todays' => 'frontend.thecore.partials.dynamic_sections.best_selling_todays',
        'banner_1' => 'frontend.thecore.partials.dynamic_sections.banner_1',
        'auction_products' => 'frontend.thecore.partials.dynamic_sections.auction_products',
        'classified_products' => 'frontend.thecore.partials.dynamic_sections.classified_products',
        'preorder_products' => 'frontend.thecore.partials.dynamic_sections.preorder_products',
        'banner_2' => 'frontend.thecore.partials.dynamic_sections.banner_2',
        'home_categories' => 'frontend.thecore.partials.dynamic_sections.home_categories',
        'newest_products' => 'frontend.thecore.partials.dynamic_sections.newest_products',
    ];
@endphp

@foreach ($layoutItems as $layoutItem)
    @continue(empty($layoutItem['enabled']))

    @php
        $sectionKey = $layoutItem['section_key'];
        $instanceId = $layoutItem['id'];
        $partial = $sectionPartialMap[$sectionKey] ?? null;
    @endphp

    @if ($partial)
        <div class="thecore-home-section" data-section-key="{{ $sectionKey }}" data-section-id="{{ $instanceId }}">
            @include($partial, ['instanceId' => $instanceId, 'layoutItem' => $layoutItem])
        </div>
    @endif
@endforeach

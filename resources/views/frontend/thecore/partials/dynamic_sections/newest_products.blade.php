@php
    $newestCustomTitle = $layoutItem['settings']['custom_title'] ?? '';
@endphp
<div data-newest-instance="{{ $instanceId }}">
    <div id="section_newest_{{ $instanceId }}" data-home-section-placeholder data-home-section="newest_products" data-section-id="{{ $instanceId }}" data-custom-title="{{ $newestCustomTitle }}"></div>
    <div class="text-center d-none view-more-container" data-view-more-container="{{ $instanceId }}">
        <button type="button" class="btn newest-load-more-btn fs-12 fs-md-16 my-32px view-more-btn" data-section-id="{{ $instanceId }}">
            {{ translate('Load More') }}
            <i class="las la-lg la-spinner la-spin d-none spinner-icon"></i>
        </button>
    </div>
</div>

@php
    $categoryId = isset($layoutItem['settings']['category_id']) ? (int) $layoutItem['settings']['category_id'] : 0;
    $customTitle = isset($layoutItem['settings']['custom_title']) ? trim((string) $layoutItem['settings']['custom_title']) : '';
@endphp
@include('frontend.thecore.partials.home_categories_single', [
    'category_id' => $categoryId,
    'instance_id' => $instanceId,
    'custom_title' => $customTitle
])

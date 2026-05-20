@props(['product', 'cardConfig' => null])
@php
    $config = $cardConfig ?? app(\App\Services\BuilderService::class)->getActiveProductCard();
    $settings = $config['settings'] ?? [];
    $layout = $config['layout'] ?? [];
@endphp
<div class="product-card" style="border: {{ !empty($settings['border']) ? '1px solid '.($settings['border_color'] ?? '#e2e8f0') : 'none' }}; border-radius: {{ $settings['border_radius'] ?? '12px' }}; background: {{ $settings['background'] ?? '#fff' }}; padding: {{ $settings['padding'] ?? '12px' }};">
@foreach($layout as $block)
    @includeIf('builder.product-card.blocks.'.$block['type'], ['block' => $block, 'product' => $product, 'children' => $block['children'] ?? []])
@endforeach
</div>

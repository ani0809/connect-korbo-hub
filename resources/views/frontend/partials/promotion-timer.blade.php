@php
    $productPromotions = $productPromotions ?? \App\Models\Promotion::active()
        ->where(function($q) use ($product) {
            $q->where('applies_to', 'all')
              ->orWhere(function($q) use ($product) {
                $q->where('applies_to', 'products')->whereJsonContains('applies_to_ids', $product->id);
              })
              ->orWhere(function($q) use ($product) {
                $q->where('applies_to', 'categories')->whereJsonContains('applies_to_ids', $product->category_id);
              });
        })->get();
@endphp
@if($productPromotions->where('type', 'flash_sale')->isNotEmpty())
@php($flashSale = $productPromotions->where('type', 'flash_sale')->first())
<div class="product-flash-timer">
    <span class="flash-icon">⚡</span>
    <span class="flash-text">Flash Sale ends in:</span>
    <div class="flash-countdown" data-countdown="{{ $flashSale->expires_at }}"></div>
</div>
@endif

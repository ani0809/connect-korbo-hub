@php
    $productPromotions = \App\Models\Promotion::active()
        ->where(function($q) use ($product) {
            $q->where('applies_to', 'all')
              ->orWhere(function($q) use ($product) {
                $q->where('applies_to', 'products')->whereJsonContains('applies_to_ids', $product->id);
              })
              ->orWhere(function($q) use ($product) {
                $q->where('applies_to', 'categories')->whereJsonContains('applies_to_ids', $product->category_id);
              });
        })
        ->whereNotNull('badge_text')
        ->take(3)->get();
@endphp

@if($productPromotions->isNotEmpty())
<div class="product-promo-badges">
    @foreach($productPromotions as $promo)
    <span class="promo-badge" style="background: {{ $promo->badge_color }}">{{ $promo->badge_text }}</span>
    @endforeach
</div>
@endif

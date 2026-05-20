@php
    $bxgy = \App\Models\Promotion::active()
        ->where('type', 'buy_x_get_y')
        ->where(function($q) use ($product) {
            $q->where('applies_to', 'all')
              ->orWhere(function($q) use ($product) {
                  $q->where('applies_to', 'products')->whereJsonContains('applies_to_ids', $product->id);
              });
        })->first();
@endphp

@if($bxgy)
@php
    $buyQty = $bxgy->conditions['buy_quantity'] ?? 2;
    $getQty = $bxgy->rewards['get_quantity'] ?? 1;
@endphp
<div class="bxgy-banner">
    <div class="bxgy-icon">🎁</div>
    <div class="bxgy-content">
        <div class="bxgy-title">Buy {{ $buyQty }} Get {{ $getQty }} Free!</div>
        <div class="bxgy-detail">Add {{ $buyQty }} or more to cart and get {{ $getQty }} {{ $getQty === 1 ? 'item' : 'items' }} free</div>
    </div>
</div>
@endif

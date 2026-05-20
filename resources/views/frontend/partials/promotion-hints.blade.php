@php
    $nearbyPromotions = \App\Models\Promotion::active()
        ->where('minimum_cart_amount', '>', 0)
        ->where('minimum_cart_amount', '<=', $cartTotal + 500)
        ->where('minimum_cart_amount', '>', $cartTotal)
        ->get();
@endphp

@foreach($nearbyPromotions as $promo)
<div class="promo-hint-banner">
    <div class="hint-icon">💡</div>
    <div class="hint-text">
        Add <strong>{{ currency_format($promo->minimum_cart_amount - $cartTotal) }}</strong> more to get:
        <strong>{{ $promo->name }}</strong>
    </div>
    <div class="hint-progress">
        <div class="hint-bar" style="width: {{ min(100, ($cartTotal / max(1, $promo->minimum_cart_amount)) * 100) }}%"></div>
    </div>
</div>
@endforeach

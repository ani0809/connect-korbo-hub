<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\ShippingArea;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Support\Facades\Cache;

class ShippingService
{
    public function calculate(Cart $cart, array $address, ?int $methodId = null): ShippingResult
    {
        $cacheKey = 'shipping:'.md5($cart->id.'|'.json_encode($address).'|'.$methodId.'|'.setting('active_shipping_method', 'flat_rate'));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($cart, $address, $methodId): ShippingResult {
            $activeMethod = setting('active_shipping_method', 'flat_rate');

            return match ($activeMethod) {
                'free' => $this->calculateFree(),
                'flat_rate' => $this->calculateFlatRate(),
                'product_wise' => $this->calculateProductWise($cart),
                'area_wise' => $this->calculateAreaWise($cart, $address),
                'seller_wise' => $this->calculateSellerWise($cart),
                'carrier_wise' => $this->calculateCarrierWise($cart, $address, $methodId),
                default => new ShippingResult(0),
            };
        });
    }

    private function calculateFree(): ShippingResult
    {
        return new ShippingResult(0, 'Free Shipping');
    }

    private function calculateFlatRate(): ShippingResult
    {
        $cost = (float) setting('flat_rate_cost', 0);
        return new ShippingResult($cost, 'Standard Delivery');
    }

    private function calculateProductWise(Cart $cart): ShippingResult
    {
        $total = 0;
        foreach ($cart->items as $item) {
            $product = $item->product;
            if (! $product || $product->shipping_type === 'free') continue;

            $cost = (float) $product->shipping_cost;
            if ((bool) $product->is_multiply_shipping) {
                $cost *= (int) $item->quantity;
            }
            $total += $cost;
        }

        return new ShippingResult((float) $total, 'Product Wise Shipping');
    }

    private function calculateAreaWise(Cart $cart, array $address): ShippingResult
    {
        $area = ShippingArea::query()->where([
            'country' => $address['country'] ?? null,
            'city' => $address['city'] ?? null,
            'is_active' => true,
        ])->when($address['area'] ?? null, fn ($q, $a) => $q->where('area', $a))->first();

        if ($area) return new ShippingResult((float) $area->shipping_cost, 'Area Wise Shipping');

        $city = ShippingArea::query()->where(['city' => $address['city'] ?? null, 'area' => null, 'is_active' => true])->first();
        if ($city) return new ShippingResult((float) $city->shipping_cost, 'City Shipping');

        $state = ShippingArea::query()->where(['state' => $address['state'] ?? null, 'city' => null, 'area' => null, 'is_active' => true])->first();
        if ($state) return new ShippingResult((float) $state->shipping_cost, 'State Shipping');

        $country = ShippingArea::query()->where(['country' => $address['country'] ?? null, 'state' => null, 'city' => null, 'area' => null, 'is_active' => true])->first();
        if ($country) return new ShippingResult((float) $country->shipping_cost, 'Country Shipping');

        return $this->calculateFlatRate();
    }

    private function calculateSellerWise(Cart $cart): ShippingResult
    {
        $total = 0;
        $sellerIds = $cart->items->whereNotNull('seller_id')->pluck('seller_id')->unique();

        foreach ($sellerIds as $sellerId) {
            $method = ShippingMethod::query()->where(['seller_id' => $sellerId, 'type' => 'seller_wise', 'is_active' => true])->first();
            if ($method) $total += (float) $method->cost;
            else $total += (float) setting('seller_wise_default_cost', 0);
        }

        return new ShippingResult((float) $total, 'Seller Wise Shipping');
    }

    private function calculateCarrierWise(Cart $cart, array $address, ?int $methodId): ShippingResult
    {
        if ($methodId) {
            $method = ShippingMethod::query()->findOrFail($methodId);
            return new ShippingResult((float) $method->cost, $method->name, $method->estimated_days);
        }

        $options = $this->getAvailableCarriers($cart, $address);
        if ($options) {
            $first = $options[0];
            return new ShippingResult((float) $first['cost'], (string) $first['name'], (string) ($first['estimated_days'] ?? ''), $options);
        }

        return new ShippingResult(0, 'No Carrier');
    }

    public function getAvailableCarriers(Cart $cart, array $address): array
    {
        $totalWeight = (float) $cart->items->sum(fn ($item) => ((float) ($item->product->weight ?? 0)) * (int) $item->quantity);
        $totalValue = (float) $cart->items->sum(fn ($item) => (float) $item->unit_price * (int) $item->quantity);
        $zone = $this->getZoneForAddress($address);

        return ShippingMethod::query()->where(['type' => 'carrier_wise', 'is_active' => true])
            ->when($zone, fn ($q) => $q->where('shipping_zone_id', $zone->id))
            ->where(function ($q) use ($totalWeight, $totalValue) {
                $q->where(function ($w) use ($totalWeight) {
                    $w->where('min_weight', '<=', $totalWeight)
                        ->where(fn ($x) => $x->whereNull('max_weight')->orWhere('max_weight', '>=', $totalWeight));
                })->orWhere(function ($p) use ($totalValue) {
                    $p->where('min_order_amount', '<=', $totalValue)
                        ->where(fn ($x) => $x->whereNull('max_order_amount')->orWhere('max_order_amount', '>=', $totalValue));
                });
            })->get()->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'cost' => (float) $m->cost,
                'estimated_days' => $m->estimated_days,
                'formatted_cost' => currency_format((float) $m->cost),
            ])->toArray();
    }

    private function getZoneForAddress(array $address): ?ShippingZone
    {
        return ShippingZone::query()->whereJsonContains('countries', $address['country'] ?? '')->where('is_active', true)->first();
    }

    public function getFreeShippingProgress(float $cartSubtotal): array
    {
        $minimum = (float) setting('free_shipping_minimum', 0);
        if ($minimum <= 0) return ['enabled' => false];

        $remaining = max(0, $minimum - $cartSubtotal);
        $percentage = min(100, ($cartSubtotal / $minimum) * 100);

        return [
            'enabled' => true,
            'minimum' => $minimum,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'achieved' => $remaining <= 0,
            'message' => $remaining > 0 ? 'Add '.currency_format($remaining).' more for FREE shipping!' : 'You qualify for FREE shipping!',
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Product;

class CommissionService
{
    public function getRate(Product $product): float
    {
        $type = setting('commission_type', 'fixed');

        return match ($type) {
            'fixed' => (float) setting('commission_rate', 0),
            'seller_based' => (float) ($product->seller?->commission_rate ?? setting('commission_rate', 0)),
            'category_based' => (float) ($product->category?->commission_rate ?? setting('commission_rate', 0)),
            default => 0.0,
        };
    }

    public function calculate(float $amount, float $rate): array
    {
        $commission = $amount * ($rate / 100);
        $sellerEarning = $amount - $commission;

        return [
            'rate' => $rate,
            'commission' => round($commission, 2),
            'seller_earning' => round($sellerEarning, 2),
        ];
    }
}

<?php

namespace App\Services;

class ShippingResult
{
    public function __construct(
        public float $cost,
        public string $name = 'Shipping',
        public ?string $estimatedDays = null,
        public array $options = [],
    ) {
    }
}

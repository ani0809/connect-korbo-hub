<?php

namespace App\Services\Courier;

use App\Models\Order;

interface CourierInterface
{
    public function createParcel(Order $order, array $options = []): array;

    public function trackParcel(string $trackingCode): array;

    public function cancelParcel(string $trackingCode): bool;

    public function getZones(): array;

    public function calculateCharge(string $zone, float $weight = 0.5, string $type = 'regular'): float;

    public function isAvailable(): bool;
}

<?php

namespace App\Services\Courier;

use App\Models\Order;

class CourierManager
{
    private static array $drivers = [
        'pathao' => PathaoService::class,
        'steadfast' => SteadfastService::class,
        'redx' => RedxService::class,
    ];

    public static function driver(string $name): CourierInterface
    {
        $class = static::$drivers[$name] ?? throw new \InvalidArgumentException("Unknown courier: {$name}");

        return new $class();
    }

    public static function default(): CourierInterface
    {
        $default = (string) setting('default_courier', 'steadfast');

        return static::driver($default);
    }

    public static function available(): array
    {
        $available = [];
        foreach (static::$drivers as $name => $class) {
            $driver = new $class();
            if ($driver->isAvailable()) {
                $available[$name] = $driver;
            }
        }

        return $available;
    }

    public static function recommend(Order $order): string
    {
        $default = (string) setting('default_courier', 'steadfast');
        if (in_array(strtolower((string) $order->shipping_city), ['dhaka', 'chittagong', 'chattogram', 'sylhet', 'rajshahi'], true)) {
            if (setting('pathao_client_id')) {
                return 'pathao';
            }
        }

        return $default;
    }
}

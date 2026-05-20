<?php

namespace App\Services;

use App\Models\License;

class LicenseKeyGenerator
{
    public function generate(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $segments = [];
            for ($i = 0; $i < 4; $i++) {
                $segment = '';
                for ($j = 0; $j < 4; $j++) {
                    $segment .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $segments[] = $segment;
            }
            $key = implode('-', $segments);
        } while (License::query()->where('license_key', $key)->exists());

        return $key;
    }
}

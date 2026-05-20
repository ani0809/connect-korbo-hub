<?php

namespace App\Traits;

use App\Models\Setting;

trait HasSettings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return Setting::getValue($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        Setting::setValue($key, $value);
    }

    public static function getGroup(string $group): array
    {
        return Setting::query()->where('group', $group)->get()->mapWithKeys(fn (Setting $s) => [$s->key => $s->typed_value])->toArray();
    }

    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::setValue((string) $key, $value);
        }
    }
}

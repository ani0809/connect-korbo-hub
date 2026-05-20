<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'autoload'];

    protected $casts = [
        'autoload' => 'boolean',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = config("settings.{$key}", $default);

        // Legacy support: some installs may still have "MyShop" in DB.
        // We want the storefront/admin to stay consistently branded as "Cibato Commerce".
        if ($key === 'site_name' && $value === 'MyShop') {
            return 'Cibato Commerce';
        }

        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value]
        );

        Cache::forget('app_settings');
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set((string) $key, $value);
        }
    }

    public static function getGroup(string $group): array
    {
        $rows = static::query()->where('group', $group)->pluck('value', 'key')->toArray();
        if (isset($rows['site_name']) && $rows['site_name'] === 'MyShop') {
            $rows['site_name'] = 'Cibato Commerce';
        }
        return $rows;
    }

    protected function castedValue(): Attribute
    {
        return Attribute::make(get: fn () => match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json' => json_decode((string) $this->value, true),
            default => $this->value,
        });
    }
}

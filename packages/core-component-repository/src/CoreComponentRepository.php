<?php

namespace MehediIitdu\CoreComponentRepository;

use App\Models\Addon;
use App\Services\CibatoLicenseService;
use Illuminate\Support\Facades\Cache;

class CoreComponentRepository
{
    public static function instantiateShopRepository(): void
    {
        $rn = CibatoLicenseService::checkActivationByDomain();
        self::finalizeRepository($rn);
    }

    protected static function finalizeRepository(?string $rn): void
    {
        if ($rn === 'bad' && env('DEMO_MODE') != 'On') {
            $url = config('services.cibato_license.activation_portal_url', 'https://licensing.cibato.com');
            redirect()->away($url)->send();
            exit;
        }
    }

    public static function initializeCache(): void
    {
        foreach (Addon::all() as $addon) {
            if ($addon->purchase_code == null) {
                self::finalizeCache($addon);
                continue;
            }

            $item_name = get_setting('item_name') ?? 'ecommerce';

            if (Cache::get($addon->unique_identifier . '-purchased', 'no') == 'no') {
                try {
                    $rn = CibatoLicenseService::checkAddonActivation(
                        $addon->unique_identifier,
                        $item_name,
                        $_SERVER['SERVER_NAME'] ?? null
                    );

                    if ($rn === 'no') {
                        self::finalizeCache($addon);
                    } elseif ($rn !== null && $rn !== false && $rn !== '') {
                        Cache::rememberForever($addon->unique_identifier . '-purchased', fn () => 'yes');
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }

    public static function finalizeCache($addon)
    {
        $addon->activated = 0;
        $addon->save();

        flash('Please reinstall ' . $addon->name . ' using a valid license code')->warning();

        return redirect()->route('addons.index')->send();
    }
}

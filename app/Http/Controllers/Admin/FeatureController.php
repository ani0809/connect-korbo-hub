<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class FeatureController extends Controller
{
    /**
     * DB keys differ from logical feature ids for legacy setting names.
     */
    private static function settingKeyForFeature(string $key): string
    {
        return match ($key) {
            'digital_products' => 'feature_digital',
            'classified_products' => 'feature_classified',
            default => 'feature_'.$key,
        };
    }

    public function index(): View
    {
        $features = FeatureService::all();

        return view('admin.features.index', compact('features'));
    }

    public function save(Request $request): RedirectResponse
    {
        FeatureService::reload();
        foreach (array_keys(FeatureService::all()) as $key) {
            Setting::query()->updateOrCreate(
                ['key' => self::settingKeyForFeature($key)],
                [
                    'value' => $request->boolean('features.'.$key) ? '1' : '0',
                    'group' => 'features',
                    'type' => 'string',
                    'autoload' => true,
                ]
            );
        }

        Cache::forget('app_settings');
        FeatureService::reload();

        return back()->with('ok', 'Feature settings saved.');
    }
}

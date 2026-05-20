<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TrackingSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.tracking', ['settings' => Setting::getGroup('tracking')]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fb_pixel_id' => 'nullable|string|max:100',
            'fb_conversion_api_token' => 'nullable|string|max:255',
            'ga4_measurement_id' => 'nullable|string|max:100',
            'ga4_api_secret' => 'nullable|string|max:255',
            'tracking_enabled' => 'nullable|boolean',
        ]);

        foreach (['fb_pixel_id', 'fb_conversion_api_token', 'ga4_measurement_id', 'ga4_api_secret', 'tracking_enabled'] as $key) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) ($data[$key] ?? ''),
                    'group' => 'tracking',
                    'type' => 'string',
                    'autoload' => true,
                ]
            );
        }
        Cache::forget('app_settings');
        return back()->with('ok', 'Tracking settings updated.');
    }
}


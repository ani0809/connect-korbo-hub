<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Courier\CourierManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourierSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.courier', ['settings' => Setting::getGroup('courier')]);
    }

    public function save(Request $request): RedirectResponse
    {
        $keys = [
            'pathao_enabled', 'pathao_client_id', 'pathao_client_secret', 'pathao_username', 'pathao_password', 'pathao_store_id', 'pathao_sandbox',
            'steadfast_enabled', 'steadfast_api_key', 'steadfast_secret_key', 'steadfast_sandbox',
            'redx_enabled', 'redx_api_key', 'redx_pickup_store_id', 'redx_sandbox',
            'default_courier', 'courier_default_weight', 'courier_auto_create',
            'courier_inside_dhaka_charge', 'courier_outside_dhaka_charge', 'courier_sub_district_charge',
            'courier_inside_dhaka_eta', 'courier_outside_dhaka_eta',
        ];

        foreach ($keys as $key) {
            $value = $request->input($key);
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) ($value ?? ''), 'group' => 'courier', 'type' => 'string', 'autoload' => true]
            );
        }

        return back()->with('ok', 'Courier settings saved.');
    }

    public function testConnection(string $courier): JsonResponse
    {
        try {
            $driver = CourierManager::driver($courier);
            if (! $driver->isAvailable()) {
                return response()->json(['success' => false, 'message' => 'Credentials missing'], 422);
            }
            $driver->getZones();
            return response()->json(['success' => true, 'message' => 'Connected']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function steadfastBalance(): JsonResponse
    {
        try {
            $driver = CourierManager::driver('steadfast');
            if (method_exists($driver, 'getBalance')) {
                $result = $driver->getBalance();
                return response()->json($result);
            }
            return response()->json(['success' => false, 'message' => 'Not supported'], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThemeSettingsController extends Controller
{
    public function index()
    {
        $groups = ['general','colors','typography','header','footer','homepage','shop','single_product','cart_checkout','my_account','blog','mobile','performance','custom_code','social','preloader','popup','not_found'];
        $settings = collect($groups)->mapWithKeys(fn ($group) => [$group => Setting::getGroup($group)])->toArray();
        return view('admin.theme-settings.index', compact('settings'));
    }

    public function save(Request $request): JsonResponse
    {
        $data = $request->except(['_token']);

        foreach ($request->allFiles() as $key => $file) {
            $data[$key] = $file->store('uploads/theme-settings', 'public');
        }

        foreach ($data as $key => $value) {
            $group = (string) str($key)->before('__');
            Setting::query()->updateOrCreate(['key' => $key], [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'group' => $group ?: 'theme',
                'type' => is_array($value) ? 'json' : 'string',
                'autoload' => true,
            ]);
        }

        Cache::forget('app_settings');

        return response()->json(['success' => true, 'message' => 'Saved!', 'data' => []]);
    }

    public function reset(Request $request): JsonResponse
    {
        $section = $request->string('section')->toString();
        if ($section !== '') {
            Setting::query()->where('group', $section)->delete();
        } else {
            Setting::query()->whereIn('group', ['general','colors','typography','header','footer','homepage','shop','single_product','cart_checkout','my_account','blog','mobile','performance','custom_code','social','preloader','popup','not_found'])->delete();
        }

        Cache::forget('app_settings');

        return response()->json(['success' => true, 'message' => 'Settings reset.', 'data' => ['section' => $section]]);
    }

    public function export(): JsonResponse
    {
        $data = Setting::query()->pluck('value', 'key')->toArray();
        return response()->json(['success' => true, 'message' => 'Export ready', 'data' => $data]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:json,txt']);
        $raw = (string) file_get_contents($request->file('file')->getRealPath());
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON structure', 'data' => []], 422);
        }

        foreach ($decoded as $key => $value) {
            Setting::query()->updateOrCreate(['key' => (string) $key], ['value' => is_array($value) ? json_encode($value) : (string) $value]);
        }

        Cache::forget('app_settings');

        return response()->json(['success' => true, 'message' => 'Settings imported', 'data' => []]);
    }
}

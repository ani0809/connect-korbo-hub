<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Services\AddonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function index()
    {
        $installed = Addon::query()->get()->keyBy('slug');
        $addonFolders = [];
        if (is_dir(base_path('addons'))) {
            $dirs = array_filter(glob(base_path('addons/*')) ?: [], 'is_dir');
            foreach ($dirs as $dir) {
                $slug = basename($dir);
                if (str_starts_with($slug, '.')) continue;
                $jsonFile = "$dir/addon.json";
                if (!file_exists($jsonFile)) continue;
                $data = json_decode((string) file_get_contents($jsonFile), true) ?: [];
                $data['installed'] = isset($installed[$slug]);
                $data['active'] = $installed[$slug]?->is_active ?? false;
                $data['db_record'] = $installed[$slug] ?? null;
                $addonFolders[$slug] = $data;
            }
        }
        return view('admin.addons.index', compact('addonFolders', 'installed'));
    }

    public function install(Request $request, AddonService $service): JsonResponse
    {
        $request->validate(['addon_key' => 'required|string', 'addon_slug' => 'required|string']);
        $verified = $service->verifyLicense($request->addon_key, $request->addon_slug);
        if (! $verified['success']) return response()->json($verified);

        $zipPath = $service->downloadAddon($request->addon_key, $request->addon_slug);
        $result = $service->install($request->addon_slug, $zipPath);
        if ($result['success']) {
            Addon::query()->where('slug', $request->addon_slug)->update(['license_key' => $request->addon_key]);
        }
        return response()->json($result);
    }

    public function uploadInstall(Request $request, AddonService $service): JsonResponse
    {
        $request->validate(['zip_file' => 'required|file|mimes:zip']);
        $zipPath = $request->file('zip_file')->store('temp', 'local');
        $fullPath = storage_path("app/{$zipPath}");
        return response()->json($service->installFromZip($fullPath));
    }

    public function activate(Request $request, AddonService $service): JsonResponse
    {
        $request->validate(['slug' => 'required|string']);
        return response()->json($service->activate($request->slug));
    }

    public function deactivate(Request $request, AddonService $service): JsonResponse
    {
        $request->validate(['slug' => 'required|string']);
        return response()->json($service->deactivate($request->slug));
    }

    public function uninstall(Request $request, AddonService $service): JsonResponse
    {
        $request->validate(['slug' => 'required|string', 'delete_data' => 'nullable|boolean']);
        return response()->json($service->uninstall($request->slug, (bool) $request->boolean('delete_data', false)));
    }

    public function settings(string $slug)
    {
        $addon = Addon::query()->where('slug', $slug)->firstOrFail();
        $addonJson = json_decode((string) file_get_contents(base_path("addons/{$slug}/addon.json")), true) ?: [];
        if (!isset($addonJson['settings_view'])) abort(404, 'No settings for this addon');
        return view($addonJson['settings_view'], compact('addon', 'addonJson'));
    }
}

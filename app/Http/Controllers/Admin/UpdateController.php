<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemUpdate;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UpdateController extends Controller
{
    public function index()
    {
        $updateInfo = Cache::get('update_check');
        $systemUpdate = SystemUpdate::query()->first();
        $currentVersion = config('shop.version');

        return view('admin.update.index', compact('updateInfo', 'systemUpdate', 'currentVersion'));
    }

    public function checkForUpdate(UpdateService $service)
    {
        return response()->json($service->checkForUpdate());
    }

    public function download(Request $request, UpdateService $service)
    {
        $version = (string) $request->string('version')->value();

        return response()->stream(function () use ($version, $service): void {
            try {
                $zipPath = $service->downloadUpdate($version);
                session(['update_zip_path' => $zipPath]);
                echo "data: ".json_encode(['success' => true, 'message' => 'Download complete', 'ready' => true])."\n\n";
            } catch (\Throwable $e) {
                echo "data: ".json_encode(['success' => false, 'message' => $e->getMessage()])."\n\n";
            }
            @ob_flush(); @flush();
        }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache', 'X-Accel-Buffering' => 'no']);
    }

    public function applyUpdate(UpdateService $service)
    {
        $zipPath = (string) session('update_zip_path');

        return response()->stream(function () use ($zipPath, $service): void {
            try {
                foreach ($service->applyUpdate($zipPath) as $progress) {
                    echo "data: ".json_encode($progress)."\n\n";
                    @ob_flush(); @flush(); usleep(200000);
                }
            } catch (\Throwable $e) {
                echo "data: ".json_encode(['step' => 'error', 'message' => $e->getMessage()])."\n\n";
                @ob_flush(); @flush();
            }
        }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache', 'X-Accel-Buffering' => 'no']);
    }

    public function uploadUpdate(Request $request)
    {
        $request->validate(['update_zip' => 'required|file|mimes:zip']);
        $zip = $request->file('update_zip');
        $zipPath = $zip->storeAs('updates', 'manual-update.zip', 'local');
        session(['update_zip_path' => storage_path("app/{$zipPath}")]);

        return response()->json(['success' => true, 'message' => 'Upload complete, ready to apply']);
    }
}

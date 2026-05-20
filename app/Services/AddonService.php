<?php

namespace App\Services;

use App\Models\Addon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddonService
{
    private string $licenseServer;

    public function __construct()
    {
        $this->licenseServer = (string) config('shop.license_server');
    }

    public function verifyLicense(string $addonKey, string $addonSlug): array
    {
        try {
            $response = Http::timeout(15)->withoutVerifying()->withHeaders(['X-API-Secret' => config('shop.license_api_secret')])->post("{$this->licenseServer}/api/addon/verify", [
                'addon_key' => $addonKey,
                'addon_slug' => $addonSlug,
                'license_key' => setting('license_key'),
                'domain' => request()->getHost(),
            ]);
            $data = $response->json();
            if ($data['valid'] ?? false) return ['success' => true, 'data' => $data, 'message' => 'License verified'];
            return ['success' => false, 'message' => $data['message'] ?? 'Invalid addon license'];
        } catch (\Throwable $e) {
            Log::error('addon_verify_failed', ['addon' => $addonSlug, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Could not connect to license server. Try again later.'];
        }
    }

    public function downloadAddon(string $addonKey, string $addonSlug): string
    {
        $res = Http::timeout(120)->withoutVerifying()->withHeaders(['X-API-Secret' => config('shop.license_api_secret')])->post("{$this->licenseServer}/api/addon/download/{$addonSlug}", [
            'addon_key' => $addonKey,
            'license_key' => setting('license_key'),
            'domain' => request()->getHost(),
        ]);
        if (! $res->successful()) throw new \RuntimeException('Addon download failed.');
        $path = storage_path('app/temp'); if (!is_dir($path)) mkdir($path, 0755, true);
        $zip = $path.'/'.$addonSlug.'-'.time().'.zip'; file_put_contents($zip, $res->body());
        return $zip;
    }

    public function installFromZip(string $zipPath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) return ['success' => false, 'message' => 'Cannot open zip file'];
        $slug = trim((string) $zip->getNameIndex(0), '/\\');
        $zip->close();
        if (!$slug) return ['success' => false, 'message' => 'Cannot detect addon slug'];
        return $this->install($slug, $zipPath);
    }

    public function install(string $addonSlug, string $zipPath): array
    {
        $targetPath = base_path("addons/{$addonSlug}");
        if (is_dir($targetPath)) return ['success' => false, 'message' => 'Addon already installed'];

        try {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) return ['success' => false, 'message' => 'Cannot open zip file'];
            $zip->extractTo(base_path('addons')); $zip->close();

            if (!file_exists("{$targetPath}/addon.json")) {
                $this->deleteDirectory($targetPath);
                return ['success' => false, 'message' => 'Invalid addon package'];
            }
            $addonJson = json_decode((string) file_get_contents("{$targetPath}/addon.json"), true) ?: [];
            $check = $this->checkRequirements($addonJson);
            if (!($check['passed'] ?? false)) { $this->deleteDirectory($targetPath); return ['success' => false, 'message' => $check['message'] ?? 'Requirement failed']; }

            $this->runAddonMigrations($addonSlug);
            Artisan::call('vendor:publish', ['--tag' => 'addon-assets', '--force' => true]);

            Addon::query()->updateOrCreate(['slug' => $addonSlug], [
                'name' => $addonJson['name'] ?? $addonSlug,
                'description' => $addonJson['description'] ?? null,
                'version' => $addonJson['version'] ?? '1.0.0',
                'author' => $addonJson['author'] ?? null,
                'is_active' => false,
                'installed_at' => now(),
            ]);
            $this->clearCaches();
            return ['success' => true, 'message' => 'Addon installed! Activate to use it.', 'addon' => $addonJson];
        } catch (\Throwable $e) {
            if (is_dir($targetPath)) $this->deleteDirectory($targetPath);
            Log::error('addon_install_failed', ['addon' => $addonSlug, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function activate(string $addonSlug): array
    {
        $addon = Addon::query()->where('slug', $addonSlug)->firstOrFail();
        if (!is_dir(base_path("addons/{$addonSlug}"))) return ['success' => false, 'message' => 'Addon files missing'];
        if ($addon->license_key) {
            $check = $this->verifyLicense($addon->license_key, $addonSlug);
            if (! $check['success']) return $check;
        }
        $addon->update(['is_active' => true]);
        $this->clearCaches();
        return ['success' => true, 'message' => 'Addon activated successfully!'];
    }

    public function deactivate(string $addonSlug): array
    {
        Addon::query()->where('slug', $addonSlug)->update(['is_active' => false]);
        $this->clearCaches();
        return ['success' => true, 'message' => 'Addon deactivated'];
    }

    public function uninstall(string $addonSlug, bool $deleteData = false): array
    {
        DB::transaction(function () use ($addonSlug, $deleteData): void {
            if ($deleteData) $this->rollbackAddonMigrations($addonSlug);
            $addonPath = base_path("addons/{$addonSlug}"); if (is_dir($addonPath)) $this->deleteDirectory($addonPath);
            $assetPath = public_path("addons/{$addonSlug}"); if (is_dir($assetPath)) $this->deleteDirectory($assetPath);
            Addon::query()->where('slug', $addonSlug)->delete();
            $this->clearCaches();
        });
        return ['success' => true, 'message' => 'Addon uninstalled'];
    }

    public function update(string $addonSlug, string $zipPath): array
    {
        $targetPath = base_path("addons/{$addonSlug}");
        $backupPath = base_path('addons/.backups/'.$addonSlug.'_'.now()->format('YmdHis'));
        if (is_dir($targetPath)) $this->copyDirectory($targetPath, $backupPath);

        try {
            $zip = new \ZipArchive(); $zip->open($zipPath);
            $tempBase = base_path('addons/.temp'); if (!is_dir($tempBase)) mkdir($tempBase, 0755, true);
            $zip->extractTo($tempBase); $zip->close();
            $tempPath = $tempBase.'/'.$addonSlug;
            if (is_dir($targetPath)) $this->deleteDirectory($targetPath);
            rename($tempPath, $targetPath);
            $this->runAddonMigrations($addonSlug);
            $addonJson = json_decode((string) file_get_contents("{$targetPath}/addon.json"), true) ?: [];
            Addon::query()->where('slug', $addonSlug)->update(['version' => $addonJson['version'] ?? '1.0.0']);
            $this->clearCaches();
            return ['success' => true, 'message' => 'Addon updated to v'.($addonJson['version'] ?? 'unknown')];
        } catch (\Throwable $e) {
            if (is_dir($backupPath)) {
                if (is_dir($targetPath)) $this->deleteDirectory($targetPath);
                rename($backupPath, $targetPath);
            }
            Log::error('addon_update_failed', ['addon' => $addonSlug, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function runAddonMigrations(string $addonSlug): void
    {
        $migrationPath = base_path("addons/{$addonSlug}/database/migrations");
        if (!is_dir($migrationPath)) return;
        Artisan::call('migrate', ['--path' => "addons/{$addonSlug}/database/migrations", '--force' => true]);
    }
    private function rollbackAddonMigrations(string $addonSlug): void
    {
        $migrationPath = base_path("addons/{$addonSlug}/database/migrations");
        if (!is_dir($migrationPath)) return;
        Artisan::call('migrate:rollback', ['--path' => "addons/{$addonSlug}/database/migrations", '--force' => true]);
    }

    private function checkRequirements(array $addonJson): array
    {
        if (isset($addonJson['requires_php']) && !version_compare(PHP_VERSION, (string) $addonJson['requires_php'], '>=')) return ['passed' => false, 'message' => 'Requires PHP '.$addonJson['requires_php'].'+'];
        if (isset($addonJson['min_app_version']) && !version_compare((string) config('shop.version'), (string) $addonJson['min_app_version'], '>=')) return ['passed' => false, 'message' => 'Requires app v'.$addonJson['min_app_version']];
        foreach (($addonJson['requires_addons'] ?? []) as $requiredSlug) {
            if (!Addon::query()->where('slug', $requiredSlug)->where('is_active', true)->exists()) return ['passed' => false, 'message' => 'Required addon not active: '.$requiredSlug];
        }
        return ['passed' => true];
    }

    private function clearCaches(): void
    {
        Cache::flush(); Artisan::call('config:clear'); Artisan::call('route:clear'); Artisan::call('view:clear');
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) return; $files = array_diff(scandir($path) ?: [], ['.', '..']);
        foreach ($files as $file) { $full = "$path/$file"; is_dir($full) ? $this->deleteDirectory($full) : @unlink($full); }
        @rmdir($path);
    }

    private function copyDirectory(string $from, string $to): void
    {
        if (!is_dir($to)) mkdir($to, 0755, true);
        $files = array_diff(scandir($from) ?: [], ['.', '..']);
        foreach ($files as $file) { $src="$from/$file"; $dst="$to/$file"; is_dir($src) ? $this->copyDirectory($src, $dst) : copy($src, $dst); }
    }
}

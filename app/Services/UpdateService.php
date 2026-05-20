<?php

namespace App\Services;

use App\Models\SystemUpdate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateService
{
    private string $licenseServer;
    private string $currentVersion;

    public function __construct()
    {
        $this->licenseServer = (string) config('shop.license_server');
        $this->currentVersion = (string) config('shop.version');
    }

    public function checkForUpdate(): array
    {
        try {
            $response = Http::timeout(10)->withoutVerifying()->withHeaders(['X-API-Secret' => config('shop.license_api_secret')])->post("{$this->licenseServer}/api/update/check", [
                'license_key' => setting('license_key'),
                'domain' => request()->getHost(),
                'current_version' => $this->currentVersion,
                'product_slug' => config('shop.slug', 'cibato-commerce'),
            ]);
            $data = $response->json() ?: ['has_update' => false];
            Cache::put('update_check', $data, 3600);
            SystemUpdate::query()->updateOrCreate(['id' => 1], ['current_version' => $this->currentVersion, 'latest_version' => $data['latest_version'] ?? null, 'last_checked_at' => now(), 'changelog' => $data['changelog'] ?? null]);
            return $data;
        } catch (\Throwable $e) {
            Log::error('update_check_failed', ['error' => $e->getMessage()]);
            return ['has_update' => false, 'error' => $e->getMessage()];
        }
    }

    public function downloadUpdate(string $version): string
    {
        $response = Http::timeout(120)->withoutVerifying()->withHeaders(['X-API-Secret' => config('shop.license_api_secret')])->post("{$this->licenseServer}/api/update/download/{$version}", ['license_key' => setting('license_key'), 'domain' => request()->getHost()]);
        if (! $response->successful()) throw new \RuntimeException('Download failed: '.$response->status());
        $dir = storage_path('app/updates'); if (!is_dir($dir)) mkdir($dir, 0755, true);
        $zipPath = storage_path("app/updates/update-{$version}.zip"); file_put_contents($zipPath, $response->body());
        return $zipPath;
    }

    public function applyUpdate(string $zipPath): \Generator
    {
        yield ['step' => 'backup', 'message' => 'Creating backup...'];
        $this->createBackup();

        yield ['step' => 'extract', 'message' => 'Extracting files...'];
        $tempPath = storage_path('app/temp/update');
        if (is_dir($tempPath)) $this->deleteDirectory($tempPath);
        mkdir($tempPath, 0755, true);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) throw new \RuntimeException('Cannot open update zip');
        $zip->extractTo($tempPath); $zip->close();

        yield ['step' => 'files', 'message' => 'Updating files...'];
        $this->copyUpdateFiles($tempPath);

        yield ['step' => 'migrate', 'message' => 'Running migrations...'];
        Artisan::call('migrate', ['--force' => true]);

        yield ['step' => 'cache', 'message' => 'Clearing caches...'];
        Artisan::call('config:clear'); Artisan::call('route:clear'); Artisan::call('view:clear'); Artisan::call('cache:clear');

        $newVersion = $this->getVersionFromZip($tempPath);
        $this->deleteDirectory($tempPath); @unlink($zipPath);
        yield ['step' => 'complete', 'message' => "Updated to v{$newVersion}!", 'version' => $newVersion];
    }

    public function updateFromUpload(string $zipPath): \Generator
    {
        yield from $this->applyUpdate($zipPath);
    }

    private function createBackup(): void
    {
        $backupPath = storage_path('app/backups/'.now()->format('Y-m-d_H-i-s')); if (!is_dir($backupPath)) mkdir($backupPath, 0755, true);
        $db = config('database.connections.mysql');
        $cmd = sprintf('mysqldump -h %s -u %s -p%s %s > %s', $db['host'], $db['username'], $db['password'], $db['database'], $backupPath.'/database.sql');
        @exec($cmd);
        file_put_contents($backupPath.'/backup_info.json', json_encode(['date' => now()->toISOString(), 'version' => $this->currentVersion, 'db_backed_up' => file_exists($backupPath.'/database.sql')]));
    }

    private function copyUpdateFiles(string $sourcePath): void
    {
        $skip = ['.env', 'storage', 'public/uploads', 'addons', 'node_modules', 'vendor'];
        $this->copyDirectorySelective($sourcePath, base_path(), $skip);
    }

    private function copyDirectorySelective(string $from, string $to, array $skip): void
    {
        $files = array_diff(scandir($from) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $src = "$from/$file"; $dst = "$to/$file";
            if (in_array($file, $skip, true) || in_array(str_replace(base_path().'/', '', $dst), $skip, true)) continue;
            if (is_dir($src)) { if (!is_dir($dst)) mkdir($dst, 0755, true); $this->copyDirectorySelective($src, $dst, $skip); }
            else copy($src, $dst);
        }
    }

    private function getVersionFromZip(string $path): string
    {
        $configFile = $path.'/config/shop.php';
        if (file_exists($configFile)) {
            $content = (string) file_get_contents($configFile);
            preg_match("/'version'\s*=>\s*'([^']+)'/", $content, $m);
            return $m[1] ?? 'unknown';
        }
        return 'unknown';
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) return;
        foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $file) {
            $full = "$path/$file";
            is_dir($full) ? $this->deleteDirectory($full) : @unlink($full);
        }
        @rmdir($path);
    }
}

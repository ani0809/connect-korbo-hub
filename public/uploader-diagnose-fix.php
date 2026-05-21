<?php
declare(strict_types=1);

/**
 * Uploader diagnose + optional auto-fix.
 *
 * Usage:
 *   /uploader-diagnose-fix.php?key=cibato-uploader-2026
 *   /uploader-diagnose-fix.php?key=cibato-uploader-2026&fix=1
 */

header('Content-Type: text/plain; charset=utf-8');

$key = $_GET['key'] ?? '';
if ($key !== 'cibato-uploader-2026') {
    http_response_code(403);
    echo "Unauthorized\n";
    exit;
}

$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    http_response_code(500);
    echo "Base path not found\n";
    exit;
}

require_once $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fixMode = isset($_GET['fix']) && $_GET['fix'] === '1';

$uploads = \App\Models\Upload::query()
    ->where('type', 'image')
    ->orderByDesc('id')
    ->limit(200)
    ->get(['id', 'file_name', 'file_original_name', 'external_link', 'created_at']);

echo "Uploader Diagnose Report\n";
echo "=======================\n";
echo "Fix mode: " . ($fixMode ? "ON" : "OFF") . "\n";
echo "Total checked: " . $uploads->count() . "\n\n";

$updated = 0;
$repaired = 0;
$ok = 0;
$broken = 0;

foreach ($uploads as $file) {
    $original = (string) $file->file_name;
    $normalized = str_replace('\\', '/', $original);

    // normalize accidental leading slash
    if (strpos($normalized, '/') === 0) {
        $normalized = ltrim($normalized, '/');
    }

    // normalize "public/uploads/..." to "uploads/..."
    if (strpos($normalized, 'public/uploads/') === 0) {
        $normalized = substr($normalized, 7);
    }

    $existsA = file_exists(public_path($normalized));
    $existsB = file_exists(public_path('public/' . $normalized));

    if ($fixMode && !$existsA && !$existsB) {
        // Try recovering missing file from storage/app path.
        $storagePath = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        if (file_exists($storagePath)) {
            $target = public_path($normalized);
            $targetDir = dirname($target);
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }
            if (@copy($storagePath, $target)) {
                $existsA = true;
                $repaired++;
            }
        }
    }

    $isOk = $existsA || $existsB || !empty($file->external_link);
    if ($isOk) {
        $ok++;
    } else {
        $broken++;
    }

    if ($fixMode && $normalized !== $original) {
        $file->file_name = $normalized;
        $file->save();
        $updated++;
    }

    if (!$isOk) {
        echo "[BROKEN] id={$file->id} path={$original}\n";
    }
}

echo "\nSummary\n";
echo "-------\n";
echo "OK rows: {$ok}\n";
echo "Broken rows: {$broken}\n";
echo "Updated rows: {$updated}\n\n";
echo "Repaired files: {$repaired}\n\n";

try {
    \Artisan::call('optimize:clear');
    echo "Cache clear: DONE\n";
} catch (\Throwable $e) {
    echo "Cache clear error: " . $e->getMessage() . "\n";
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "OPcache reset attempted\n";
}

echo "\nDone\n";

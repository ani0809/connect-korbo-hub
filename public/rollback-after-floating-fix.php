<?php
/**
 * One-click cleanup for rollback baseline:
 * update-floating-buttons-stable-align-fix-2026-04-15.zip
 *
 * Usage:
 * /rollback-after-floating-fix.php?key=cibato-rollback-2026
 */

declare(strict_types=1);

@header('Content-Type: text/plain; charset=utf-8');

$key = $_GET['key'] ?? '';
if ($key !== 'cibato-rollback-2026') {
    http_response_code(403);
    echo "Unauthorized.\n";
    exit;
}

$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    http_response_code(500);
    echo "Base path not found.\n";
    exit;
}

$targets = [
    'public/css/skeleton-loader.css',
    'public/js/skeleton.js',
    'resources/views/components/skeleton.blade.php',
    'resources/views/components/skeletons',
    'skeleton-rollout-status-2026-04-15.md',
];

function rrmdir(string $dir): bool
{
    if (!is_dir($dir)) {
        return true;
    }
    $items = scandir($dir);
    if ($items === false) {
        return false;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (!rrmdir($path)) {
                return false;
            }
        } else {
            @unlink($path);
        }
    }
    return @rmdir($dir);
}

$deleted = [];
$missing = [];
$failed = [];

foreach ($targets as $target) {
    $full = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $target);
    if (!file_exists($full)) {
        $missing[] = $target;
        continue;
    }
    if (is_dir($full)) {
        if (rrmdir($full)) {
            $deleted[] = $target;
        } else {
            $failed[] = $target;
        }
    } else {
        if (@unlink($full)) {
            $deleted[] = $target;
        } else {
            $failed[] = $target;
        }
    }
}

echo "Rollback cleanup summary\n";
echo "========================\n";
echo "Deleted:\n";
foreach ($deleted as $d) {
    echo " - {$d}\n";
}
echo "Missing:\n";
foreach ($missing as $m) {
    echo " - {$m}\n";
}
echo "Failed:\n";
foreach ($failed as $f) {
    echo " - {$f}\n";
}

if (empty($failed)) {
    echo "\nCleanup completed.\n";
} else {
    http_response_code(500);
    echo "\nCleanup completed with errors.\n";
}


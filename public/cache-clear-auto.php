<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

/**
 * One-click cache clear utility for hosting without terminal access.
 *
 * Usage:
 *   /cache-clear-auto.php?key=YOUR_KEY
 * Optional:
 *   &delete=1   -> self delete this file after successful run
 */

@set_time_limit(120);
@ini_set('memory_limit', '512M');

header('Content-Type: text/plain; charset=utf-8');

$appKey = (string) getenv('APP_CACHE_CLEAR_KEY');
$fallbackKey = 'cibato-cache-clear-2026';
$validKey = $appKey !== '' ? $appKey : $fallbackKey;
$providedKey = isset($_GET['key']) ? (string) $_GET['key'] : '';

if ($providedKey === '' || !hash_equals($validKey, $providedKey)) {
    http_response_code(403);
    echo "Forbidden: invalid key.\n";
    echo "Use: ?key=YOUR_KEY\n";
    exit;
}

$candidates = [
    dirname(__DIR__),                    // standard: project/public -> project
    dirname(__DIR__, 2),                 // nested public directory
    dirname(__DIR__, 3),                 // deeply nested document root
    __DIR__,                             // fallback current directory
];

$basePath = null;
foreach ($candidates as $candidate) {
    if (
        file_exists($candidate . '/vendor/autoload.php') &&
        file_exists($candidate . '/bootstrap/app.php')
    ) {
        $basePath = $candidate;
        break;
    }
}

echo "Starting cache clear...\n\n";

if ($basePath !== null) {
    echo "Laravel root detected: {$basePath}\n\n";

    require $basePath . '/vendor/autoload.php';
    $app = require $basePath . '/bootstrap/app.php';

    /** @var Kernel $kernel */
    $kernel = $app->make(Kernel::class);

    $commands = [
        'optimize:clear',
        'cache:clear',
        'view:clear',
        'config:clear',
        'route:clear',
        'event:clear',
    ];

    foreach ($commands as $command) {
        try {
            $exit = $kernel->call($command);
            echo sprintf("[%s] exit=%d\n", $command, $exit);
            $output = trim($kernel->output());
            if ($output !== '') {
                echo $output . "\n";
            }
            echo "\n";
        } catch (\Throwable $e) {
            echo sprintf("[%s] failed: %s\n\n", $command, $e->getMessage());
        }
    }
} else {
    // Fallback for shared hosting where vendor/bootstrap may be outside this document root.
    echo "Laravel autoload not found from this path.\n";
    echo "Trying manual cache directory cleanup fallback...\n\n";

    $fallbackBase = dirname(__DIR__);
    $paths = [
        $fallbackBase . '/storage/framework/cache/data',
        $fallbackBase . '/storage/framework/views',
        $fallbackBase . '/storage/framework/sessions',
        $fallbackBase . '/bootstrap/cache',
    ];

    $deleted = 0;
    $failed = 0;

    $deleteTree = function (string $dir) use (&$deleted, &$failed): void {
        if (!is_dir($dir)) {
            echo "[skip] {$dir} (not found)\n";
            return;
        }
        $items = @scandir($dir);
        if ($items === false) {
            echo "[fail] {$dir} (cannot read)\n";
            $failed++;
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $subItems = @scandir($path);
                if ($subItems !== false && count($subItems) <= 2) {
                    @rmdir($path);
                } else {
                    // Recursive delete for nested cache directories.
                    $stack = [$path];
                    while ($stack) {
                        $current = array_pop($stack);
                        $entries = @scandir($current);
                        if ($entries === false) {
                            $failed++;
                            continue;
                        }
                        $hasChildDir = false;
                        foreach ($entries as $entry) {
                            if ($entry === '.' || $entry === '..') {
                                continue;
                            }
                            $child = $current . DIRECTORY_SEPARATOR . $entry;
                            if (is_dir($child)) {
                                $stack[] = $current;
                                $stack[] = $child;
                                $hasChildDir = true;
                                break;
                            }
                            if (@unlink($child)) {
                                $deleted++;
                            } else {
                                $failed++;
                            }
                        }
                        if (!$hasChildDir && @rmdir($current)) {
                            $deleted++;
                        }
                    }
                }
                continue;
            }
            if (@unlink($path)) {
                $deleted++;
            } else {
                $failed++;
            }
        }
        echo "[ok] cleaned {$dir}\n";
    };

    foreach ($paths as $path) {
        $deleteTree($path);
    }

    echo "\nFallback cleanup completed. deleted={$deleted}, failed={$failed}\n";
    echo "Note: If vendor/autoload is outside web root, artisan commands cannot run from this script.\n\n";
}

if (function_exists('opcache_reset')) {
    try {
        $ok = @opcache_reset();
        echo 'opcache_reset: ' . ($ok ? "done" : "not available/failed") . "\n";
    } catch (\Throwable $e) {
        echo "opcache_reset failed: {$e->getMessage()}\n";
    }
}

echo "\nCompleted.\n";

if (isset($_GET['delete']) && (string) $_GET['delete'] === '1') {
    $self = __FILE__;
    if (@unlink($self)) {
        echo "Self delete: done\n";
    } else {
        echo "Self delete: failed (check file permission)\n";
    }
}


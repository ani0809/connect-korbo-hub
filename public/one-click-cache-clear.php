<?php
declare(strict_types=1);

/**
 * Run Laravel cache clear from browser after zip extract.
 * Usage:
 *   /one-click-cache-clear.php?key=cibato-cache-2026
 */

header('Content-Type: text/plain; charset=utf-8');

$key = $_GET['key'] ?? '';
if ($key !== 'cibato-cache-2026') {
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
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$commands = [
    'optimize:clear',
    'route:clear',
    'view:clear',
    'config:clear',
    'cache:clear',
];

foreach ($commands as $command) {
    echo ">>> php artisan {$command}\n";
    try {
        $code = $kernel->call($command);
        echo trim($kernel->output()) . "\n";
        echo "exit_code: {$code}\n\n";
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "OPcache reset attempted\n";
}

echo "\nDone\n";

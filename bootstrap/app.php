<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        $appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production';

        $csrfExcept = [
            '*media-library/*',
            'admin/media-library/*',
            'seller/media-library/*',
            'account/media-library/*',
            'admin/editor-media/*',
        ];
        if ($appEnv === 'local') {
            // Local-only fail-safe: prevent 419 on admin login caused by stale dev cookies/sessions.
            $csrfExcept[] = 'admin/login';
        }
        $middleware->validateCsrfTokens(except: $csrfExcept);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\CachePageMiddleware::class,
            \App\Http\Middleware\MinifyHtmlMiddleware::class,
            \App\Http\Middleware\TrackVisitorMiddleware::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\InstalledMiddleware::class,
        ]);

        $middleware->alias([
            'installed' => \App\Http\Middleware\InstalledMiddleware::class,
            'not.installed' => \App\Http\Middleware\NotInstalledMiddleware::class,
            'auth.admin' => \App\Http\Middleware\AdminMiddleware::class,
            'auth.seller' => \App\Http\Middleware\SellerMiddleware::class,
            'auth.customer' => \App\Http\Middleware\CustomerMiddleware::class,
            'license' => \App\Http\Middleware\LicenseMiddleware::class,
            'staff.route' => \App\Http\Middleware\StaffRouteAccessMiddleware::class,
            'can.staff' => \App\Http\Middleware\StaffPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Browsers / JSON viewer extensions often send Accept: application/json.
            // For non-API routes, force HTML for 404 so /, /admin, /__health are not raw JSON.
            if ($e instanceof NotFoundHttpException && ! $request->is('api/*')) {
                $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $app = htmlspecialchars((string) config('app.name'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $html = <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>404 — {$app}</title></head><body style="font-family:system-ui,sans-serif;max-width:42rem;margin:2rem auto;padding:0 1rem;line-height:1.5"><h1 style="font-size:1.35rem">404 — Page not found</h1><p style="color:#333">{$msg}</p><p><a href="/">Home</a> · <a href="/admin">Admin login</a> · <a href="/install">Installer</a></p><p style="color:#666;font-size:.9rem">Use <strong>http://127.0.0.1:8000</strong> with <code>php artisan serve</code> (port 8000). Port <strong>5173</strong> is only the Vite asset server.</p></body></html>
HTML;

                return response($html, 404)->header('Content-Type', 'text/html; charset=UTF-8');
            }

            // Only use structured JSON for real API routes under /api/*
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'code' => 422,
                ], 422);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'errors' => [],
                    'code' => 404,
                ], 404);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not found',
                    'errors' => [],
                    'code' => 404,
                ], 404);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login.',
                    'errors' => [],
                    'code' => 401,
                ], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized',
                    'errors' => [],
                    'code' => 403,
                ], 403);
            }

            if ($e instanceof ThrottleRequestsException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Slow down.',
                    'errors' => [],
                    'code' => 429,
                ], 429);
            }

            $message = config('app.debug') ? $e->getMessage() : 'Internal server error';

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [],
                'code' => 500,
            ], 500);
        });
    })->create();

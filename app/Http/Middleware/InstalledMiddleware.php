<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstalledMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = file_exists(storage_path('installed.lock'));

        if ($request->is('install*')) {
            if ($isInstalled) {
                return redirect('/');
            }

            return $next($request);
        }

        if (! $isInstalled) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application is not installed.',
                    'errors' => [],
                    'code' => 503,
                ], 503);
            }

            return redirect('/install');
        }

        return $next($request);
    }
}

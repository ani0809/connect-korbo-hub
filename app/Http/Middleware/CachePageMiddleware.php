<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CachePageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->method() !== 'GET' || auth()->check() || $request->is('cart*', 'checkout*', 'account*', 'admin*', 'seller*') || !setting('page_cache_enabled', false)) return $next($request);
        $key = 'page_'.md5($request->fullUrl()).'_'.app()->getLocale();
        $ttl = (int) setting('page_cache_ttl', 60) * 60;
        if (Cache::has($key)) return response((string) Cache::get($key))->header('X-Cache', 'HIT');
        $response = $next($request);
        if ($response->getStatusCode() === 200) Cache::put($key, (string) $response->getContent(), $ttl);
        return $response->header('X-Cache', 'MISS');
    }
}

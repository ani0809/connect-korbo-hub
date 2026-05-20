<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin*', 'seller*', 'api*', 'install*')) {
            return $next($request);
        }

        if (setting('cookie_consent_required', false) && $request->session()->get('cookie_consent') === 'rejected') {
            return $next($request);
        }

        if (! $request->hasSession()) {
            return $next($request);
        }

        $sessionKey = 'visitor_'.$request->session()->getId();

        if (! Cache::has($sessionKey)) {
            $cur = (int) Cache::get('active_visitors', 0);
            Cache::put('active_visitors', $cur + 1, now()->addMinutes(30));
            Cache::put($sessionKey, 1, now()->addMinutes(30));
        } else {
            Cache::put($sessionKey, 1, now()->addMinutes(30));
        }

        return $next($request);
    }
}

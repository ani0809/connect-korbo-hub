<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LicenseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $service = app(LicenseService::class);

        if (! $service->isValid()) {
            session()->flash('license_warning', 'License is inactive or unreachable. The store remains accessible.');
        }

        return $next($request);
    }
}

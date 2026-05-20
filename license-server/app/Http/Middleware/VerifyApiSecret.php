<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = (string) $request->header('X-API-Secret');
        $expected = (string) env('LICENSE_API_SECRET', 'change-me');

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized API request'], 401);
        }

        return $next($request);
    }
}

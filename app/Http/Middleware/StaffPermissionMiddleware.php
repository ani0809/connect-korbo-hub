<?php

namespace App\Http\Middleware;

use App\Support\StaffPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return redirect()->route('admin.login');
        }

        $user = $request->user();

        if (! in_array($user->role ?? null, ['admin', 'staff'], true)) {
            abort(403);
        }

        if (! StaffPermissions::has($user, $permission)) {
            abort(403, "You don't have permission: {$permission}");
        }

        return $next($request);
    }
}

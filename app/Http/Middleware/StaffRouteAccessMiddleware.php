<?php

namespace App\Http\Middleware;

use App\Support\StaffPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Maps named admin routes to a single StaffPermissions key.
 * Admins bypass; staff must have the mapped permission.
 */
class StaffRouteAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('admin.login');
        }

        if (($user->role ?? null) === 'admin') {
            return $next($request);
        }

        if (($user->role ?? null) !== 'staff') {
            abort(403);
        }

        $name = $request->route()?->getName();
        if ($name === null) {
            return $next($request);
        }

        /** @var string|null $permission */
        $permission = config('admin_route_permissions.'.$name);

        if ($permission === null) {
            abort(403, 'This admin route is not configured for staff access.');
        }

        if (! StaffPermissions::has($user, $permission)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

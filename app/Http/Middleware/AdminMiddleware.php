<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        if (! in_array($user->role ?? null, ['admin', 'staff'], true)) {
            abort(403);
        }

        if (($user->role ?? null) === 'staff' && ($user->status ?? null) !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->with('error', 'Your staff account is inactive.');
        }

        view()->share('staffPermissions', $user->staff_permissions ?? []);
        view()->share('isAdmin', ($user->role ?? null) === 'admin');
        view()->share('isStaff', ($user->role ?? null) === 'staff');

        return $next($request);
    }
}

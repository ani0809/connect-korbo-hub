<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function stop(Request $request): RedirectResponse
    {
        $adminId = session('impersonator_id');
        if (! $adminId) {
            return redirect('/');
        }

        $request->session()->forget(['impersonating_as', 'impersonator_id']);

        Auth::loginUsingId((int) $adminId);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'Returned to admin.');
    }
}

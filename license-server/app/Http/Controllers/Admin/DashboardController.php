<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseActivation;

class DashboardController extends Controller
{
    public function index()
    {
        $total = License::count();
        $active = License::where('status', 'active')->count();
        $expired = License::where('status', 'expired')->count();
        $recentActivations = LicenseActivation::with('license')->latest()->limit(10)->get();
        $revenue = License::with('plan')->get()->sum(fn ($l) => (float) ($l->plan->price ?? 0));

        return view('admin.dashboard', compact('total', 'active', 'expired', 'recentActivations', 'revenue'));
    }
}

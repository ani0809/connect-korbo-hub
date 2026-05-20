<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;

class AnalyticsController extends Controller
{
    public function index(GoogleAnalyticsService $googleAnalyticsService)
    {
        $period = request('period', '30daysAgo');
        $data = $googleAnalyticsService->getReport($period);
        return view('admin.analytics.index', compact('data', 'period'));
    }
}

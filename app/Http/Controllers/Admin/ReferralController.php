<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(): View
    {
        $referrals = Referral::query()->with(['referrer:id,name,email', 'referred:id,name,email'])->latest()->paginate(30);

        $stats = [
            'total' => Referral::count(),
            'completed' => Referral::where('status', 'rewarded')->count(),
            'total_points_given' => (float) (Referral::sum('referrer_reward_value') + Referral::sum('referred_reward_value')),
            'top_referrers' => Referral::where('status', 'rewarded')
                ->select('referrer_id')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('referrer_id')
                ->with('referrer:id,name')
                ->orderByDesc('count')
                ->take(5)
                ->get(),
        ];

        return view('admin.referrals.index', compact('referrals', 'stats'));
    }
}

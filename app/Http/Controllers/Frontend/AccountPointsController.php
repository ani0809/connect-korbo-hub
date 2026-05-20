<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AccountPointsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $points = DB::table('club_point_transactions')->where('user_id', $user->id)->orderByDesc('created_at')->paginate(20);
        $balance = (int) DB::table('club_point_transactions')->where('user_id', $user->id)->sum('points');

        $rules = [
            'earn_rate' => setting('points_earn_rate', '1 point per $1'),
            'redeem_rate' => setting('points_redeem_rate', '100 points = $1'),
            'min_redeem' => (int) setting('points_min_redeem', 100),
            'expiry_days' => (int) setting('points_expiry_days', 365),
        ];

        return view('frontend.account.points', compact('points', 'balance', 'rules'));
    }
}

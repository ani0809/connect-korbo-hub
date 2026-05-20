<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Str;

class ReferralService
{
    public function generateCode(User $user): string
    {
        if ($user->referral_code) {
            return (string) $user->referral_code;
        }

        $base = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($user->name)), 0, 4));
        $base = str_pad($base, 4, 'X');
        $code = $base.strtoupper(Str::random(4));

        while (User::query()->where('referral_code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }

        $user->update(['referral_code' => $code]);

        return $code;
    }

    public function getReferralUrl(User $user): string
    {
        return url('/register?ref='.$this->generateCode($user));
    }

    public function trackVisit(string $code, string $ip): void
    {
        session(['referral_code' => strtoupper(trim($code)), 'referral_ip' => $ip]);
        config(['session.lifetime' => max((int) config('session.lifetime', 120), 60 * 24 * 30)]);
    }

    public function processRegistration(User $newUser): void
    {
        $code = session('referral_code');
        if (! $code) {
            return;
        }

        $referrer = User::query()->where('referral_code', $code)->first();
        if (! $referrer || $referrer->id === $newUser->id) {
            return;
        }

        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $newUser->id,
            'referral_code' => $code,
            'status' => 'pending',
            'ip_address' => (string) session('referral_ip'),
            'signed_up_at' => now(),
            'referred_reward_type' => 'points',
            'referred_reward_value' => (float) (int) setting('referral_referred_bonus_points', 100),
        ]);

        $newUser->update(['referred_by' => $referrer->id]);
        $referredBonus = (int) setting('referral_referred_bonus_points', 100);

        if ($referredBonus > 0) {
            app(ClubPointsService::class)->addBonus($newUser->id, $referredBonus, 'Welcome bonus (referral signup)!');
        }

        session()->forget(['referral_code', 'referral_ip']);
    }

    public function processFirstOrder(User $user, Order $order): void
    {
        if (! $user->referred_by) {
            return;
        }

        $minimumOrder = (float) setting('referral_min_order_amount', 0);
        if ((float) $order->total < $minimumOrder) {
            return;
        }

        $referral = Referral::query()->where(['referred_id' => $user->id, 'status' => 'pending'])->first();
        if (! $referral) {
            return;
        }

        $hasPrevious = Order::query()->where('user_id', $user->id)->where('id', '<>', $order->id)->exists();
        if ($hasPrevious) {
            return;
        }

        $referrerBonus = (int) setting('referral_referrer_bonus_points', 200);
        if ($referrerBonus > 0) {
            app(ClubPointsService::class)->addBonus($referral->referrer_id, $referrerBonus, 'Referral reward: your friend placed first order.');
            app(NotificationService::class)->send('referral.rewarded', User::find($referral->referrer_id), [
                'points' => $referrerBonus,
                'friend_name' => $user->name,
            ]);
        }

        $referral->update([
            'status' => 'rewarded',
            'first_order_at' => now(),
            'referrer_reward_type' => 'points',
            'referrer_reward_value' => $referrerBonus,
        ]);
    }

    public function getStats(User $user): array
    {
        $referrals = Referral::query()->where('referrer_id', $user->id)->get();

        return [
            'total_referrals' => $referrals->count(),
            'completed' => $referrals->where('status', 'rewarded')->count(),
            'pending' => $referrals->where('status', 'pending')->count(),
            'total_points_earned' => (float) $referrals->where('status', 'rewarded')->sum('referrer_reward_value'),
            'referral_url' => $this->getReferralUrl($user),
            'referral_code' => $this->generateCode($user),
        ];
    }
}

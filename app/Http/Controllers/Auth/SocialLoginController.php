<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirect(string $provider)
    {
        $providers = ['google', 'facebook', 'twitter', 'apple'];
        abort_unless(in_array($provider, $providers, true), 404);
        abort_unless((bool) setting("{$provider}_login_enabled", false), 404);
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, CartService $cartService)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('login')->with('error', 'Social login failed. Try again.');
        }

        $user = User::query()->where('email', $socialUser->getEmail())->first();
        if (! $user) {
            $user = User::query()->create([
                'name' => $socialUser->getName() ?: 'User',
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(32)),
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
                'role' => 'customer',
            ]);
        }

        auth()->login($user, true);
        $cartService->mergeGuestCart($user->id);

        return redirect()->intended('/');
    }
}

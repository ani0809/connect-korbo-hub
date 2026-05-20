<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\ClubPointsService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        if (! $this->isProviderEnabled($provider)) {
            return redirect()->route('login')->with('error', 'Social login not available');
        }

        return Socialite::driver($provider)->scopes($this->getScopes($provider))->redirect();
    }

    public function callback(string $provider)
    {
        if (! $this->isProviderEnabled($provider)) {
            return redirect()->route('login')->with('error', 'Social login not available');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::error('Social auth failed', ['provider' => $provider, 'error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Social login failed. Please try again.');
        }

        $user = $this->findOrCreateUser($provider, $socialUser);
        if (! $user) {
            return redirect()->route('login')->with('error', 'Could not authenticate. Please try again.');
        }
        if (($user->status ?? 'active') === 'banned') {
            return redirect()->route('login')->with('error', 'Your account has been suspended.');
        }

        Auth::login($user, true);
        $user->update(['last_login_at' => now(), 'last_login_ip' => request()->ip()]);

        return redirect(session()->pull('url.intended', route('account.dashboard')));
    }

    private function findOrCreateUser(string $provider, $socialUser): ?User
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            $providerId = (string) $socialUser->getId();
            $email = $socialUser->getEmail();
            $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
            $avatarPath = $this->storeAvatarLocally((string) $socialUser->getAvatar(), $provider, $providerId);
            $encryptedToken = $socialUser->token ? encrypt((string) $socialUser->token) : null;
            $encryptedRefreshToken = ! empty($socialUser->refreshToken) ? encrypt((string) $socialUser->refreshToken) : null;

            $socialAccount = SocialAccount::query()
                ->where(['provider' => $provider, 'provider_id' => $providerId])
                ->with('user')
                ->first();

            if ($socialAccount) {
                $socialAccount->update([
                    'provider_email' => $email,
                    'provider_name' => $name,
                    'provider_avatar' => $avatarPath ?: $socialAccount->provider_avatar,
                    'access_token' => $encryptedToken,
                    'refresh_token' => $encryptedRefreshToken,
                ]);
                if ($socialAccount->user && $avatarPath && ! $socialAccount->user->avatar) {
                    $socialAccount->user->update(['avatar' => $avatarPath]);
                }
                return $socialAccount->user;
            }

            $existingUser = null;
            if ($email) {
                $existingUser = User::query()->where('email', $email)->first();
            }

            if ($existingUser) {
                SocialAccount::query()->create([
                    'user_id' => $existingUser->id,
                    'provider' => $provider,
                    'provider_id' => $providerId,
                    'provider_email' => $email,
                    'provider_name' => $name,
                    'provider_avatar' => $avatarPath,
                    'access_token' => $encryptedToken,
                    'refresh_token' => $encryptedRefreshToken,
                ]);
                if ($avatarPath && ! $existingUser->avatar) {
                    $existingUser->update(['avatar' => $avatarPath]);
                }
                return $existingUser;
            }

            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'role' => 'customer',
                'status' => 'active',
                'email_verified_at' => $email ? now() : null,
                'avatar' => $avatarPath,
            ]);

            SocialAccount::query()->create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $providerId,
                'provider_email' => $email,
                'provider_name' => $name,
                'provider_avatar' => $avatarPath,
                'access_token' => $encryptedToken,
                'refresh_token' => $encryptedRefreshToken,
            ]);

            app(NotificationService::class)->send('customer.welcome', $user, ['name' => $user->name]);
            if ((int) setting('points_registration_bonus', 0) > 0) {
                app(ClubPointsService::class)->addBonus($user->id, (int) setting('points_registration_bonus'), '🎁 Welcome bonus points!');
            }

            return $user;
        });
    }

    private function storeAvatarLocally(string $avatarUrl, string $provider, string $providerId): ?string
    {
        if ($avatarUrl === '') return null;
        try {
            $response = Http::timeout(20)->get($avatarUrl);
            if (! $response->successful()) return null;
            $ext = 'jpg';
            $contentType = strtolower((string) $response->header('Content-Type'));
            if (str_contains($contentType, 'png')) $ext = 'png';
            if (str_contains($contentType, 'webp')) $ext = 'webp';
            $path = "avatars/social/{$provider}-{$providerId}-".time().".{$ext}";
            Storage::disk('public')->put($path, $response->body());
            return $path;
        } catch (\Throwable $e) {
            Log::warning('social avatar download failed', ['provider' => $provider, 'provider_id' => $providerId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function isProviderEnabled(string $provider): bool
    {
        return match ($provider) {
            'google' => (bool) setting('social_google_enabled', false) && (bool) config('services.google.client_id'),
            'facebook' => (bool) setting('social_facebook_enabled', false) && (bool) config('services.facebook.client_id'),
            default => false,
        };
    }

    private function getScopes(string $provider): array
    {
        return match ($provider) {
            'google' => ['openid', 'email', 'profile'],
            'facebook' => ['email', 'public_profile'],
            default => [],
        };
    }
}

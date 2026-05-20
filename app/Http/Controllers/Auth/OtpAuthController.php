<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClubPointsService;
use App\Services\NotificationService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OtpAuthController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|max:20', 'type' => 'required|in:login,register,forgot']);
        $phone = $this->normalizePhone((string) $request->phone);

        if (! $this->isValidBangladeshPhone($phone)) {
            return response()->json(['success' => false, 'message' => 'Invalid phone number. Please use a valid Bangladeshi number.']);
        }

        $rateLimitKey = 'otp_send_'.$phone.'_'.$request->ip();
        $maxAttempts = (int) setting('otp_max_resend_attempts', 5);
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json(['success' => false, 'message' => 'Too many attempts. Please wait '.ceil($seconds / 60).' minutes.']);
        }

        if ($request->type === 'login' || $request->type === 'forgot') {
            $user = User::query()->where('phone', $phone)->first();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'No account found with this phone number. Please register first.', 'action' => 'register']);
            }
            if (($user->status ?? 'active') === 'banned') {
                return response()->json(['success' => false, 'message' => 'Account suspended. Contact support.']);
            }
        }
        if ($request->type === 'register' && User::query()->where('phone', $phone)->exists()) {
            return response()->json(['success' => false, 'message' => 'Phone already registered. Please login instead.', 'action' => 'login']);
        }

        app(OtpService::class)->sendViaSms($phone);
        RateLimiter::hit($rateLimitKey, 3600);
        Log::info('OTP sent to: '.$this->maskPhone($phone));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to '.$this->maskPhone($phone),
            'expires_in' => (int) setting('otp_expiry_minutes', 10) * 60,
        ]);
    }

    public function verifyLogin(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string', 'otp' => 'required|string|size:6']);
        $phone = $this->normalizePhone((string) $request->phone);
        $lockKey = 'otp_verify_login_'.$phone;

        if (RateLimiter::tooManyAttempts($lockKey, 5)) {
            return response()->json(['success' => false, 'message' => 'Too many wrong attempts. Please wait 15 minutes.'], 429);
        }

        $verified = app(OtpService::class)->verify($phone, (string) $request->otp, 'phone');
        if (! $verified) {
            RateLimiter::hit($lockKey, 900);
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP. Please try again.']);
        }

        RateLimiter::clear($lockKey);
        $user = User::query()->where('phone', $phone)->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Account not found.']);
        }

        Auth::login($user, true);
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'phone_verified_at' => $user->phone_verified_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'redirect' => session()->pull('url.intended', route('account.dashboard')),
        ]);
    }

    public function verifyRegister(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        $phone = $this->normalizePhone((string) $request->phone);
        $lockKey = 'otp_verify_register_'.$phone;
        if (RateLimiter::tooManyAttempts($lockKey, 5)) {
            return response()->json(['success' => false, 'message' => 'Too many wrong attempts. Please wait 15 minutes.'], 429);
        }

        $verified = app(OtpService::class)->verify($phone, (string) $request->otp, 'phone');
        if (! $verified) {
            RateLimiter::hit($lockKey, 900);
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.']);
        }
        RateLimiter::clear($lockKey);

        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => bcrypt((string) $request->password),
            'role' => 'customer',
            'status' => 'active',
            'phone_verified_at' => now(),
            'email_verified_at' => $request->email ? null : now(),
        ]);

        app(NotificationService::class)->send('customer.welcome', $user, ['name' => $user->name]);
        if ((int) setting('points_registration_bonus', 0) > 0) {
            app(ClubPointsService::class)->addBonus($user->id, (int) setting('points_registration_bonus'), '🎁 Registration bonus!');
        }
        Auth::login($user, true);

        return response()->json(['success' => true, 'message' => 'Account created successfully!', 'redirect' => route('account.dashboard')]);
    }

    public function forgotViaPhone(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string', 'otp' => 'required|string|size:6', 'password' => 'required|min:6|confirmed']);
        $phone = $this->normalizePhone((string) $request->phone);
        $lockKey = 'otp_verify_forgot_'.$phone;
        if (RateLimiter::tooManyAttempts($lockKey, 5)) {
            return response()->json(['success' => false, 'message' => 'Too many wrong attempts. Please wait 15 minutes.'], 429);
        }

        $verified = app(OtpService::class)->verify($phone, (string) $request->otp, 'phone');
        if (! $verified) {
            RateLimiter::hit($lockKey, 900);
            return response()->json(['success' => false, 'message' => 'Invalid OTP.']);
        }
        RateLimiter::clear($lockKey);

        $updated = User::query()->where('phone', $phone)->update(['password' => bcrypt((string) $request->password), 'remember_token' => null]);
        if (! $updated) {
            return response()->json(['success' => false, 'message' => 'Account not found.']);
        }

        return response()->json(['success' => true, 'message' => 'Password reset successfully!']);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if (str_starts_with($phone, '880')) $phone = '0'.substr($phone, 3);
        if (strlen($phone) === 10) $phone = '0'.$phone;
        return $phone;
    }

    private function isValidBangladeshPhone(string $phone): bool
    {
        return (bool) preg_match('/^01[3-9][0-9]{8}$/', $phone);
    }

    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 2).str_repeat('X', max(0, strlen($phone) - 4)).substr($phone, -2);
    }
}

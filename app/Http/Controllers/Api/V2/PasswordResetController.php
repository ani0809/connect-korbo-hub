<?php

namespace App\Http\Controllers\Api\V2;

use App\Notifications\AppEmailVerificationNotification;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use App\Notifications\PasswordResetRequest;
use Illuminate\Support\Str;
use App\Http\Controllers\OTPVerificationController;
use App\Rules\Recaptcha;
use Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetController extends Controller
{
    private function apiResponse(bool $result, $message, int $status = 200, array $extra = [])
    {
        return response()->json(array_merge([
            'result' => $result,
            'success' => $result,
            'status' => $status,
            'message' => $message,
        ], $extra), $status);
    }

    public function forgetRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'send_code_by' => 'required|in:email,phone',
            'email_or_phone' => 'required',
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_forgot_password') == 1, ['required', new Recaptcha()], ['sometimes'])
            ],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, $validator->errors()->all(), 422);
        }

        if ($request->send_code_by == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)->first();
        }


        if (!$user) {
            return $this->apiResponse(false, translate('User is not found'), 404);
        }

        if ($user) {
            $throttleKey = 'api_auth_forget_request:' . $request->ip() . ':' . mb_strtolower(trim((string) $request->email_or_phone));
            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                return $this->apiResponse(false, translate('Too many requests. Please try again later.'), 429);
            }

            RateLimiter::hit($throttleKey, 120);
            $user->verification_code = rand(100000, 999999);
            $user->save();
            if ($request->send_code_by == 'phone') {

                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            } else {
                try {

                    $user->notify(new AppEmailVerificationNotification());
                } catch (\Exception $e) {
                    Log::warning('Password reset email notification failed', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $this->apiResponse(true, translate('A code is sent'), 200);
    }

    public function confirmReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verify_by' => 'required|in:email,phone',
            'email_or_phone' => 'required',
            'verification_code' => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, $validator->errors()->all(), 422);
        }

        $attemptKey = 'api_auth_confirm_reset:' . $request->ip() . ':' . mb_strtolower(trim((string) $request->email_or_phone));
        if (RateLimiter::tooManyAttempts($attemptKey, 8)) {
            return $this->apiResponse(false, translate('Too many attempts. Please try again later.'), 429);
        }
        RateLimiter::hit($attemptKey, 300);

        if ($request->verify_by == 'email') {
            $user = User::where('email', $request->email_or_phone)
                ->where('verification_code', $request->verification_code)
                ->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)
                ->where('verification_code', $request->verification_code)
                ->first();
        }

        if ($user != null) {
            $user->verification_code = null;
            $user->password = Hash::make($request->password);
            $user->save();
            RateLimiter::clear($attemptKey);
            return $this->apiResponse(true, translate('Your password is reset.Please login'), 200);
        } else {
            return $this->apiResponse(false, translate('No user is found'), 404);
        }
    }

    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verify_by' => 'required|in:email,phone',
            'email_or_phone' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, $validator->errors()->all(), 422);
        }

        $throttleKey = 'api_auth_resend_code:' . $request->ip() . ':' . mb_strtolower(trim((string) $request->email_or_phone));
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return $this->apiResponse(false, translate('Too many requests. Please try again later.'), 429);
        }
        RateLimiter::hit($throttleKey, 120);

        if ($request->verify_by == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)->first();
        }


        if (!$user) {
            return $this->apiResponse(false, translate('User is not found'), 404);
        }

        $user->verification_code = rand(100000, 999999);
        $user->save();

        if ($request->verify_by == 'email') {
            try {
                $user->notify(new AppEmailVerificationNotification());
            } catch (\Exception $e) {
                Log::warning('Password reset resend email notification failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }



        return $this->apiResponse(true, translate('A code is sent again'), 200);
    }
}

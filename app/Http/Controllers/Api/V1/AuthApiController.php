<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthApiController extends BaseApiController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required_without:phone|nullable|email',
            'phone' => 'required_without:email|nullable|string',
            'password' => 'required|string',
        ]);

        $user = User::query()
            ->when($request->filled('email'), fn ($q) => $q->where('email', $request->string('email')))
            ->when(! $request->filled('email'), fn ($q) => $q->where('phone', $request->string('phone')))
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        if ($user->status === 'banned') {
            return $this->error('Account suspended', 403);
        }

        $user->tokens()->where('name', 'mobile-app')->delete();

        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
            'user' => new UserResource($user),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required_without:phone|nullable|email|unique:users,email',
            'phone' => 'required_without:email|nullable|string|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'role' => 'customer',
            'status' => 'active',
        ]);

        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Registration successful', 201);
    }

    public function forgotPassword(Request $request, OtpService $otp): JsonResponse
    {
        $request->validate([
            'email' => 'required_without:phone|nullable|email',
            'phone' => 'required_without:email|nullable|string',
        ]);

        try {
            if ($request->filled('email')) {
                $otp->sendViaEmail($request->string('email'));
            } else {
                $otp->sendViaSms($request->string('phone'));
            }
        } catch (\Throwable) {
            return $this->error('Could not send verification code', 422);
        }

        return $this->success(null, 'If the account exists, a verification code has been sent.');
    }

    public function verifyOtp(Request $request, OtpService $otp): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|string|size:6',
            'type' => 'required|in:email,phone',
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if (! $otp->verify($request->identifier, $request->otp, $request->type)) {
            return $this->error('Invalid or expired code', 422);
        }

        $user = User::query()
            ->when($request->type === 'email', fn ($q) => $q->where('email', $request->identifier))
            ->when($request->type === 'phone', fn ($q) => $q->where('phone', $request->identifier))
            ->first();

        if (! $user) {
            return $this->error('User not found', 404);
        }

        if ($request->filled('password')) {
            $user->update(['password' => $request->password]);
        }

        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

        return $this->success([
            'verified' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function socialLogin(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:google,facebook',
            'access_token' => 'required|string',
        ]);

        try {
            $abstractUser = match ($request->provider) {
                'google' => Socialite::driver('google')->userFromToken($request->access_token),
                'facebook' => Socialite::driver('facebook')->userFromToken($request->access_token),
            };
        } catch (\Throwable $e) {
            return $this->error('Social authentication failed', 401);
        }

        $email = $abstractUser->getEmail();
        if (! $email) {
            return $this->error('Email not provided by provider', 422);
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $abstractUser->getName() ?: 'Customer',
                'password' => str()->random(32),
                'role' => 'customer',
                'status' => 'active',
            ]
        );

        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|nullable|string|max:50',
            'preferred_locale' => 'sometimes|nullable|string|max:10',
        ]);

        $request->user()->update($request->only(['name', 'phone', 'preferred_locale']));

        return $this->success(new UserResource($request->user()->fresh()), 'Profile updated');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();
        if (! Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect', 422);
        }

        $user->update(['password' => $request->password]);

        return $this->success(null, 'Password changed');
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();

        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
        ]);
    }
}

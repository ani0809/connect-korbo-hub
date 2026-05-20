<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function verify(string $token, string $action = 'submit'): bool
    {
        if (!setting('recaptcha_enabled', false)) return true;
        $response = Http::post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => setting('recaptcha_secret_key'),
            'response' => $token,
            'remoteip' => request()->ip(),
        ]);
        $data = $response->json();
        return ($data['success'] ?? false) && ($data['score'] ?? 0) >= (float) setting('recaptcha_threshold', 0.5) && ($data['action'] ?? '') === $action;
    }
}

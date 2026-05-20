<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function generate(string $identifier, string $type = 'email'): string
    {
        OtpVerification::query()->where(['identifier' => $identifier, 'type' => $type])->delete();

        $length = max(4, min(6, (int) setting('otp_length', 6)));
        $min = (int) pow(10, $length - 1);
        $max = (int) pow(10, $length) - 1;
        $otp = str_pad((string) random_int($min, $max), $length, '0', STR_PAD_LEFT);

        OtpVerification::query()->create([
            'identifier' => $identifier,
            'type' => $type,
            'otp' => $otp,
            'expires_at' => now()->addMinutes((int) setting('otp_expiry_minutes', 10)),
        ]);

        return $otp;
    }

    public function verify(string $identifier, string $otp, string $type = 'email'): bool
    {
        $record = OtpVerification::query()
            ->where(['identifier' => $identifier, 'type' => $type, 'is_used' => false])
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $record) {
            return false;
        }

        // Constant-time compare to reduce timing leak.
        if (! hash_equals((string) $record->otp, (string) $otp)) {
            return false;
        }

        // Delete OTP after successful verification.
        $record->delete();
        return true;
    }

    public function sendViaEmail(string $email): string
    {
        $otp = $this->generate($email, 'email');
        Mail::to($email)->queue(new OtpMail($otp));
        return $otp;
    }

    public function sendViaSms(string $phone): string
    {
        $otp = $this->generate($phone, 'phone');
        app(SmsService::class)->sendOtp($phone, $otp);
        return $otp;
    }
}

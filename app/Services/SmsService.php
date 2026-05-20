<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsService
{
    public function send(string $phone, string $message): bool
    {
        $gateway = (string) setting('sms_gateway', 'twilio');

        if (! setting('sms_enabled', false)) {
            return false;
        }

        $message = Str::limit(trim(strip_tags($message)), 160, '');

        $status = match ($gateway) {
            'twilio' => $this->sendViaTwilio($phone, $message),
            'nexmo' => $this->sendViaNexmo($phone, $message),
            'msg91' => $this->sendViaMsg91($phone, $message),
            'infobip' => $this->sendViaInfobip($phone, $message),
            'custom' => $this->sendViaCustomApi($phone, $message),
            default => false,
        };

        Log::info('sms_send', ['phone' => $phone, 'gateway' => $gateway, 'success' => $status]);

        return $status;
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        $message = str_replace('[[otp]]', $otp, (string) setting('sms_otp_template', 'Your OTP is [[otp]]. Valid for 10 minutes. Do not share with anyone.'));
        return $this->send($phone, $message);
    }

    private function sendViaTwilio(string $phone, string $message): bool
    {
        try {
            $sid = (string) setting('twilio_account_sid');
            $response = Http::withBasicAuth($sid, (string) setting('twilio_auth_token'))->asForm()->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => setting('twilio_from_number'),
                'To' => $phone,
                'Body' => $message,
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Twilio SMS failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendViaNexmo(string $phone, string $message): bool
    {
        try {
            $response = Http::post('https://rest.nexmo.com/sms/json', [
                'api_key' => setting('nexmo_api_key'),
                'api_secret' => setting('nexmo_api_secret'),
                'to' => $phone,
                'from' => setting('nexmo_from_name', setting('site_name')),
                'text' => $message,
            ]);
            $data = $response->json();
            return ($data['messages'][0]['status'] ?? '1') === '0';
        } catch (\Throwable $e) {
            Log::error('Nexmo SMS failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendViaMsg91(string $phone, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'authkey' => setting('msg91_authkey'),
                'content-type' => 'application/json',
            ])->post('https://api.msg91.com/api/v5/flow/', [
                'template_id' => setting('msg91_template_id'),
                'sender' => setting('msg91_sender_id'),
                'mobiles' => $phone,
                'OTP' => $message,
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('MSG91 SMS failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendViaInfobip(string $phone, string $message): bool
    {
        try {
            $base = rtrim((string) setting('infobip_base_url'), '/');
            $response = Http::withToken((string) setting('infobip_api_key'))->post($base.'/sms/2/text/advanced', [
                'messages' => [[
                    'from' => setting('infobip_from', setting('site_name', 'Cibato Commerce')),
                    'destinations' => [['to' => $phone]],
                    'text' => $message,
                ]],
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Infobip SMS failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendViaCustomApi(string $phone, string $message): bool
    {
        $apiUrl = (string) setting('sms_custom_api_url');
        $method = strtoupper((string) setting('sms_custom_api_method', 'GET'));
        $params = json_decode((string) setting('sms_custom_api_params', '{}'), true) ?: [];

        $params = array_map(function ($val) use ($phone, $message) {
            return str_replace(['[[phone]]', '[[message]]'], [$phone, $message], (string) $val);
        }, $params);

        try {
            $response = $method === 'POST' ? Http::post($apiUrl, $params) : Http::get($apiUrl, $params);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Custom SMS failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

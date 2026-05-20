<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private string $serverKey;
    private string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = (string) setting('firebase_server_key', '');
    }

    public function sendToUser(int $userId, string $title, string $body, string $url = '/'): bool
    {
        $tokens = DB::table('fcm_tokens')->where('user_id', $userId)->pluck('token')->toArray();
        if ($tokens === []) {
            return false;
        }
        return $this->sendToTokens($tokens, $title, $body, $url);
    }

    public function sendToAll(string $title, string $body, string $url = '/'): bool
    {
        return $this->sendToTopic('all_users', $title, $body, $url);
    }

    public function sendToTokens(array $tokens, string $title, string $body, string $url = '/'): bool
    {
        if ($this->serverKey === '') {
            return false;
        }

        foreach (array_chunk($tokens, 500) as $chunk) {
            try {
                Http::withHeaders([
                    'Authorization' => 'key='.$this->serverKey,
                    'Content-Type' => 'application/json',
                ])->post($this->fcmUrl, [
                    'registration_ids' => $chunk,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'icon' => url(asset((string) setting('favicon', 'favicon.ico'))),
                        'click_action' => $url,
                        'sound' => 'default',
                    ],
                    'data' => ['url' => $url, 'click_action' => $url],
                    'webpush' => [
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'icon' => url(asset((string) setting('favicon', 'favicon.ico'))),
                        ],
                        'fcm_options' => ['link' => $url],
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::error('Push notification failed', ['error' => $e->getMessage()]);
                return false;
            }
        }

        return true;
    }

    private function sendToTopic(string $topic, string $title, string $body, string $url = '/'): bool
    {
        try {
            Http::withHeaders([
                'Authorization' => 'key='.$this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, [
                'to' => '/topics/'.$topic,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => ['url' => $url],
            ]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}

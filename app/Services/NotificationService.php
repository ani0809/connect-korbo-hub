<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Mail\GenericMail;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationService
{
    public function send(string $templateSlug, ?User $user, array $data = [], array $channels = []): void
    {
        $template = NotificationTemplate::query()->where('slug', $templateSlug)->where('is_active', true)->first();
        if (! $template) {
            return;
        }

        $activeChannels = $channels !== [] ? $channels : ((array) ($template->channels ?? ['database']));
        $subject = $this->replaceVars((string) ($template->subject ?? ''), $data);
        $body = $this->replaceVars((string) $template->body, $data);

        foreach ($activeChannels as $channel) {
            match ($channel) {
                'email' => $this->sendEmail($user, $subject, $body, $data),
                'sms' => $this->sendSms($user, $body, $data),
                'push' => $this->sendPush($user, $subject, $body, $data),
                'database' => $this->saveToDatabase($user, $templateSlug, $subject, $body, $data),
                default => null,
            };
        }
    }

    public function sendBulk(string $templateSlug, Collection $users, array $data = []): void
    {
        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }

            SendNotificationJob::dispatch($templateSlug, $user->id, $data)->onQueue('notifications');
        }
    }

    private function replaceVars(string $text, array $data): string
    {
        $text = str_replace('[[site_name]]', (string) setting('site_name', config('app.name')), $text);
        $text = str_replace('[[site_url]]', url('/'), $text);
        $text = str_replace('[[current_year]]', date('Y'), $text);

        foreach ($data as $key => $value) {
            $text = str_replace('[['.$key.']]', (string) $value, $text);
        }

        return $text;
    }

    private function sendEmail(?User $user, string $subject, string $body, array $data): void
    {
        $email = $data['email'] ?? $user?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->queue(new GenericMail($subject, $body, $data));
        } catch (\Throwable $e) {
            Log::error('Email send failed', ['email' => $email, 'error' => $e->getMessage()]);
        }
    }

    private function sendSms(?User $user, string $body, array $data): void
    {
        $phone = $data['phone'] ?? $user?->phone;
        if (! $phone) {
            return;
        }

        $smsBody = Str::limit(trim(strip_tags($body)), 160, '');
        app(SmsService::class)->send($phone, $smsBody);
    }

    private function sendPush(?User $user, string $title, string $body, array $data): void
    {
        if (! $user) {
            return;
        }

        app(PushNotificationService::class)->sendToUser((int) $user->id, $title, trim(strip_tags($body)), (string) ($data['push_url'] ?? url('/')));
    }

    private function saveToDatabase(?User $user, string $type, string $title, string $body, array $data): void
    {
        if (! $user) {
            return;
        }

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => [
                'title' => $title,
                'body' => trim(strip_tags($body)),
                'url' => $data['notification_url'] ?? null,
                'icon' => $data['notification_icon'] ?? null,
            ],
        ]);
    }
}

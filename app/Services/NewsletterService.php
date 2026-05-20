<?php

namespace App\Services;

use App\Jobs\SendNewsletterCampaignJob;
use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NewsletterService
{
    public function subscribe(string $email, ?string $name = null): array
    {
        $existing = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($existing) {
            if ($existing->unsubscribed_at) {
                $existing->update(['unsubscribed_at' => null, 'name' => $name ?? $existing->name]);
                return ['success' => true, 'message' => 'Welcome back! You are resubscribed.'];
            }
            return ['success' => false, 'message' => 'You are already subscribed!'];
        }

        NewsletterSubscriber::query()->create([
            'email' => $email,
            'name' => $name,
            'is_verified' => ! setting('newsletter_double_optin', false),
            'subscribed_at' => now(),
        ]);

        return ['success' => true, 'message' => (string) setting('newsletter_success_message', 'Thanks for subscribing! ??')];
    }

    public function unsubscribe(string $token): bool
    {
        try {
            $data = decrypt($token);
            NewsletterSubscriber::query()->where('email', $data['email'])->update(['unsubscribed_at' => now()]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendCampaign(int $campaignId): void
    {
        $campaign = NewsletterCampaign::query()->findOrFail($campaignId);
        $campaign->update(['status' => 'sending']);

        $recipients = match ($campaign->recipients) {
            'all' => NewsletterSubscriber::query()->where('is_verified', true)->whereNull('unsubscribed_at')->get()->map(fn ($n) => (object) ['email' => $n->email, 'name' => $n->name]),
            'customers' => User::query()->where('role', 'customer')->whereNotNull('email')->get()->map(fn ($u) => (object) ['email' => $u->email, 'name' => $u->name]),
            'sellers' => User::query()->where('role', 'seller')->whereNotNull('email')->get()->map(fn ($u) => (object) ['email' => $u->email, 'name' => $u->name]),
            'custom' => collect(json_decode((string) ($campaign->recipient_list ?? '[]'), true) ?: [])->map(fn ($email) => (object) ['email' => $email, 'name' => null]),
            default => collect(),
        };

        $sent = 0;
        $failed = 0;

        foreach ($recipients->chunk(100) as $chunk) {
            foreach ($chunk as $recipient) {
                try {
                    $unsubToken = encrypt(['email' => $recipient->email, 'expires' => now()->addYear()->timestamp]);
                    Mail::to($recipient->email)->queue(new NewsletterMail((string) $campaign->subject, (string) $campaign->content, (string) ($recipient->name ?? ''), $unsubToken));
                    $sent++;
                } catch (\Throwable) {
                    $failed++;
                }
            }
            sleep(1);
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);
    }

    public function queueCampaign(int $campaignId): void
    {
        SendNewsletterCampaignJob::dispatch($campaignId)->onQueue('newsletters');
    }
}

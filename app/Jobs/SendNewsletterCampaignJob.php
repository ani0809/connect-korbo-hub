<?php

namespace App\Jobs;

use App\Services\NewsletterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewsletterCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [60, 180, 600, 1800];

    public function __construct(public int $campaignId) {}

    public function handle(NewsletterService $newsletterService): void
    {
        $newsletterService->sendCampaign($this->campaignId);
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(6);
    }

    public function failed(\Throwable $exception): void
    {
        \App\Models\NewsletterCampaign::query()->where('id', $this->campaignId)->update([
            'status' => 'failed',
            'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\Log::error('newsletter_campaign_failed', [
            'campaign_id' => $this->campaignId,
            'error' => $exception->getMessage(),
        ]);
    }
}

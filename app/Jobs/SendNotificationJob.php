<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [30, 120, 300];

    public function __construct(public string $templateSlug, public int $userId, public array $data = []) {}

    public function handle(NotificationService $service): void
    {
        $user = User::query()->find($this->userId);
        if (! $user) return;
        $service->send($this->templateSlug, $user, $this->data);
    }

    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('notification_job_failed', [
            'template' => $this->templateSlug,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}

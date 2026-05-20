<?php

use App\Models\ActivityLog;
use App\Models\Cart;
use App\Models\NewsletterCampaign;
use App\Models\Order;
use App\Models\OtpVerification;
use App\Models\PriceTracker;
use App\Models\Shipment;
use App\Models\User;
use App\Jobs\GenerateSitemapJob;
use App\Jobs\SendNewsletterCampaignJob;
use App\Models\Language;
use App\Models\WarehouseStock;
use App\Services\AccountingService;
use App\Services\AbandonedCartService;
use App\Services\ClubPointsService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Services\SitemapService;
use App\Services\UpdateService;
use App\Services\Courier\CourierManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Schedule;

Schedule::command('cache:warmup')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('license:ping')->dailyAt('00:00')->withoutOverlapping();

Schedule::call(function (): void {
    Cart::query()->where('updated_at', '<', now()->subDays(30))->whereDoesntHave('user')->delete();
})->name('cleanup.guest.carts')->daily()->withoutOverlapping();

Schedule::call(function (): void {
    $tempPath = storage_path('app/temp');
    if (is_dir($tempPath)) {
        foreach (glob("{$tempPath}/*") ?: [] as $file) {
            if (filemtime($file) < time() - 86400) {
                is_file($file) ? @unlink($file) : null;
            }
        }
    }
})->name('cleanup.temp.files')->daily()->withoutOverlapping();

Schedule::call(function (): void {
    try {
        app(UpdateService::class)->checkForUpdate();
    } catch (\Throwable) {
    }
})->name('updates.check')->weekly()->withoutOverlapping();

Schedule::call(function (): void {
    app(AbandonedCartService::class)->sendRecoveryEmails();
})->name('abandoned-cart.recovery-email')->hourly()->withoutOverlapping();

Schedule::call(function (): void {
    PriceTracker::query()->where('is_notified', false)->with(['user', 'product'])->chunk(100, function ($trackers): void {
        foreach ($trackers as $tracker) {
            if (! $tracker->product || ! $tracker->user) continue;
            $currentPrice = (float) ($tracker->product->current_price ?? $tracker->product->main_price ?? 0);
            if ($currentPrice > (float) $tracker->target_price) continue;

            app(NotificationService::class)->send('price.dropped', $tracker->user, [
                'product_name' => $tracker->product->name,
                'target_price' => currency_format((float) $tracker->target_price),
                'current_price' => currency_format($currentPrice),
                'product_url' => route('product.show', $tracker->product->slug),
            ]);
            $tracker->update(['is_notified' => true]);
        }
    });
})->name('price-tracker.notify')->hourly()->withoutOverlapping();

Schedule::call(function (): void {
    if (class_exists(ActivityLog::class)) {
        ActivityLog::query()->where('created_at', '<', now()->subDays(90))->delete();
    }
    DB::table('api_logs')->where('created_at', '<', now()->subDays(30))->delete();
})->name('cleanup.activity-and-api-logs')->monthly()->withoutOverlapping();

Schedule::job(new GenerateSitemapJob())->twiceDaily(1, 13)->withoutOverlapping();

Schedule::call(function (): void {
    app(ClubPointsService::class)->expirePoints();
})->name('club-points.expire')->dailyAt('02:00')->withoutOverlapping();

Schedule::call(function (): void {
    app(ClubPointsService::class)->notifyPointsExpiringSoon();
})->name('club-points.expiring-notify')->dailyAt('09:00')->withoutOverlapping();

Schedule::call(function (): void {
    $bonus = (int) setting('points_birthday_bonus', 0);
    if ($bonus <= 0 || ! Schema::hasColumn('users', 'date_of_birth')) {
        return;
    }

    User::query()
        ->where('role', 'customer')
        ->whereNotNull('date_of_birth')
        ->whereRaw('DATE_FORMAT(date_of_birth, "%m-%d") = DATE_FORMAT(NOW(), "%m-%d")')
        ->chunk(100, function ($users) use ($bonus): void {
            foreach ($users as $user) {
                $given = DB::table('club_point_transactions')
                    ->where('user_id', $user->id)
                    ->where('type', 'bonus')
                    ->where('description', 'like', '%Birthday bonus%')
                    ->whereYear('created_at', now()->year)
                    ->exists();
                if ($given) {
                    continue;
                }
                app(ClubPointsService::class)->addBonus((int) $user->id, $bonus, '🎂 Birthday bonus points!');
            }
        });
})->name('club-points.birthday')->dailyAt('06:00')->withoutOverlapping();

Schedule::call(function (): void {
    $autoDays = (int) setting('auto_complete_days', 0);
    if ($autoDays > 0) {
        Order::query()
            ->where('order_status', 'delivered')
            ->where('delivered_at', '<', now()->subDays($autoDays))
            ->update(['order_status' => 'completed']);
    }

    OtpVerification::query()->where('expires_at', '<', now())->delete();

    $importPath = storage_path('app/imports');
    if (is_dir($importPath)) {
        foreach (glob($importPath.'/*') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < time() - 86400) {
                @unlink($file);
            }
        }
    }
})->name('maintenance.daily')->dailyAt('02:15')->withoutOverlapping();

Schedule::call(function (): void {
    $campaigns = NewsletterCampaign::query()
        ->where('status', 'scheduled')
        ->where('scheduled_at', '<=', now())
        ->get();

    foreach ($campaigns as $campaign) {
        dispatch(new SendNewsletterCampaignJob($campaign->id))->onQueue('newsletters');
    }
})->name('newsletter.scheduled')->everyFifteenMinutes()->withoutOverlapping();

Schedule::call(function (): void {
    if (Schema::hasTable('notifications')) {
        DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', now()->subDays(60))
            ->delete();
    }
})->name('cleanup.notifications')->weeklyOn(0, '03:30')->withoutOverlapping();

Schedule::call(function (): void {
    Shipment::query()
        ->whereIn('status', ['created', 'picked', 'in_transit', 'out_for_delivery'])
        ->whereNotNull('tracking_code')
        ->chunkById(100, function ($shipments): void {
            foreach ($shipments as $shipment) {
                try {
                    $result = CourierManager::driver((string) $shipment->courier)->trackParcel((string) $shipment->tracking_code);
                    if (! ($result['success'] ?? false)) {
                        continue;
                    }
                    $status = (string) ($result['status'] ?? $shipment->status);
                    $shipment->update([
                        'status' => $status,
                        'tracking_history' => $result['events'] ?? [],
                        'last_tracked_at' => now(),
                        'delivered_at' => $status === 'delivered' ? now() : $shipment->delivered_at,
                    ]);
                    if ($status === 'delivered') {
                        Order::query()->where('id', $shipment->order_id)->update(['order_status' => 'delivered', 'delivered_at' => now()]);
                    }
                } catch (\Throwable) {
                }
            }
        });
})->name('shipments.auto-track')->everyTwoHours()->withoutOverlapping();

Schedule::call(function (): void {
    Order::query()
        ->where('order_status', 'delivered')
        ->whereNotNull('delivered_at')
        ->where('delivered_at', '<', now()->subHours(4))
        ->update(['order_status' => 'completed']);
})->name('orders.auto-complete-delivered')->everyTwoHours()->withoutOverlapping();

Schedule::command('model:prune')->daily()->withoutOverlapping();

Schedule::call(function (): void {
    if (! class_exists(SitemapService::class)) {
        return;
    }
    $xml = app(SitemapService::class)->generate();
    file_put_contents(public_path('sitemap.xml'), $xml);
})->name('sitemap.weekly-rebuild')->weekly()->withoutOverlapping();

Schedule::call(function (): void {
    if (! class_exists(Language::class) || ! class_exists(\App\Http\Controllers\Admin\TranslationController::class)) {
        return;
    }
    $languages = Language::query()->where('is_active', true)->get();
    foreach ($languages as $lang) {
        app(\App\Http\Controllers\Admin\TranslationController::class)->rebuildJsonFile($lang->code);
    }
})->name('translations.rebuild')->weekly()->withoutOverlapping();

Schedule::call(function (): void {
    if (! class_exists(WarehouseStock::class) || ! class_exists(InventoryService::class)) {
        return;
    }
    WarehouseStock::query()->chunk(100, function ($stocks): void {
        foreach ($stocks as $stock) {
            app(InventoryService::class)->syncProductStock($stock->product_id, $stock->product_variant_id);
        }
    });
})->name('inventory.sync-from-warehouse')->daily()->withoutOverlapping();

Schedule::call(function (): void {
    if (! (bool) setting('accounting_auto_journal', true)) {
        return;
    }
    if (! class_exists(AccountingService::class)) {
        return;
    }
    $alreadyJournaledOrderIds = DB::table('journals')
        ->where('reference_type', 'Order')
        ->whereNotNull('reference_id')
        ->pluck('reference_id')
        ->all();

    Order::query()
        ->where('payment_status', 'paid')
        ->when(! empty($alreadyJournaledOrderIds), fn ($q) => $q->whereNotIn('id', $alreadyJournaledOrderIds))
        ->take(50)
        ->get()
        ->each(function (Order $order): void {
            try {
                app(AccountingService::class)->recordSale($order);
            } catch (\Throwable $e) {
                Log::error('Auto journal failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            }
        });
})->name('accounting.auto-journal-hourly')->hourly()->withoutOverlapping();

Artisan::command('inspire', function (): void {
    $this->comment('Build with confidence.');
})->purpose('Display inspiration')->hourly();

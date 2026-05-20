<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillMediaAssetsCommand extends Command
{
    protected $signature = 'media:backfill {--dry-run : Preview only without DB writes}';
    protected $description = 'Backfill media_assets and *_media_id fields from legacy path-based uploads';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $count = 0;

        $count += $this->backfillProducts($dryRun);
        $count += $this->backfillProductImages($dryRun);
        $count += $this->backfillSellers($dryRun);
        $count += $this->backfillUsers($dryRun);
        $count += $this->backfillReviews($dryRun);

        $this->info("Backfill completed. Updated records: {$count}".($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }

    private function ensureAsset(?string $path, ?int $uploaderId, bool $dryRun): ?MediaAsset
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        if ($dryRun) {
            return new MediaAsset(['id' => 0, 'path' => $path, 'disk' => 'public']);
        }
        $existing = MediaAsset::query()->where('disk', 'public')->where('path', $path)->first();
        if ($existing) {
            return $existing;
        }
        $disk = Storage::disk('public');
        $mime = $disk->exists($path) ? (string) $disk->mimeType($path) : null;
        $size = $disk->exists($path) ? (int) $disk->size($path) : 0;
        $mediaType = str_starts_with((string) $mime, 'image/') ? 'image' : (str_starts_with((string) $mime, 'video/') ? 'video' : 'other');
        return MediaAsset::query()->create([
            'uploader_id' => $uploaderId,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $mime,
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'size' => $size,
            'media_type' => $mediaType,
            'title' => pathinfo($path, PATHINFO_FILENAME),
        ]);
    }

    private function backfillProducts(bool $dryRun): int
    {
        $updated = 0;
        Product::query()->whereNull('thumbnail_media_id')->whereNotNull('thumbnail')->chunkById(200, function ($rows) use (&$updated, $dryRun): void {
            foreach ($rows as $product) {
                $asset = $this->ensureAsset($product->thumbnail, $product->seller?->user_id ?? null, $dryRun);
                if (! $asset) continue;
                $updated++;
                if (! $dryRun) {
                    $product->thumbnail_media_id = $asset->id;
                    $product->save();
                }
            }
        });
        return $updated;
    }

    private function backfillProductImages(bool $dryRun): int
    {
        $updated = 0;
        ProductImage::query()->whereNull('media_asset_id')->whereNotNull('image')->chunkById(200, function ($rows) use (&$updated, $dryRun): void {
            foreach ($rows as $row) {
                $asset = $this->ensureAsset($row->image, null, $dryRun);
                if (! $asset) continue;
                $updated++;
                if (! $dryRun) {
                    $row->media_asset_id = $asset->id;
                    $row->save();
                }
            }
        });
        return $updated;
    }

    private function backfillSellers(bool $dryRun): int
    {
        $updated = 0;
        Seller::query()->chunkById(200, function ($rows) use (&$updated, $dryRun): void {
            foreach ($rows as $seller) {
                $changed = false;
                if (! $seller->shop_logo_media_id && $seller->shop_logo) {
                    $asset = $this->ensureAsset($seller->shop_logo, $seller->user_id, $dryRun);
                    if ($asset) {
                        $seller->shop_logo_media_id = $asset->id;
                        $changed = true;
                    }
                }
                if (! $seller->shop_banner_media_id && $seller->shop_banner) {
                    $asset = $this->ensureAsset($seller->shop_banner, $seller->user_id, $dryRun);
                    if ($asset) {
                        $seller->shop_banner_media_id = $asset->id;
                        $changed = true;
                    }
                }
                if ($changed) {
                    $updated++;
                    if (! $dryRun) $seller->save();
                }
            }
        });
        return $updated;
    }

    private function backfillUsers(bool $dryRun): int
    {
        $updated = 0;
        User::query()->whereNull('avatar_media_id')->whereNotNull('avatar')->chunkById(200, function ($rows) use (&$updated, $dryRun): void {
            foreach ($rows as $user) {
                $asset = $this->ensureAsset($user->avatar, $user->id, $dryRun);
                if (! $asset) continue;
                $updated++;
                if (! $dryRun) {
                    $user->avatar_media_id = $asset->id;
                    $user->save();
                }
            }
        });
        return $updated;
    }

    private function backfillReviews(bool $dryRun): int
    {
        $updated = 0;
        Review::query()->whereNotNull('images')->chunkById(200, function ($rows) use (&$updated, $dryRun): void {
            foreach ($rows as $review) {
                if (! empty($review->image_media_ids)) {
                    continue;
                }
                $ids = [];
                foreach ((array) $review->images as $path) {
                    $asset = $this->ensureAsset($path, $review->user_id, $dryRun);
                    if ($asset && $asset->id) {
                        $ids[] = $asset->id;
                    }
                }
                if ($ids === []) continue;
                $updated++;
                if (! $dryRun) {
                    $review->image_media_ids = $ids;
                    $review->save();
                }
            }
        });
        return $updated;
    }
}


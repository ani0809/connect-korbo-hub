<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheWarmup extends Command
{
    protected $signature = 'cache:warmup';
    protected $description = 'Warm up application caches';

    public function handle(): int
    {
        $this->info('Warming caches...');
        Setting::query()->where('autoload', true)->get();
        $this->info('Settings cached');
        Cache::rememberForever('all_categories_en', fn () => Category::query()->where('is_active', true)->with('children')->whereNull('parent_id')->get());
        $this->info('Categories cached');
        Cache::rememberForever('active_brands_en', fn () => Brand::query()->where('is_active', true)->get());
        $this->info('Brands cached');
        $this->info('Cache warmup complete!');
        return self::SUCCESS;
    }
}

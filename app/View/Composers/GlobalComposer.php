<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Currency;
use App\Models\FlashDeal;
use App\Models\Language;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GlobalComposer
{
    public function compose(View $view): void
    {
        // During installation or early bootstrap, avoid querying non-existent tables.
        if (! file_exists(storage_path('installed.lock'))) {
            $view->with('megaMenuCategories', collect());
            $view->with('menuItems', collect());
            $view->with('mobileMenuItems', collect());
            $view->with('currencies', collect());
            $view->with('languages', collect());
            $view->with('activeFlashDeal', null);
            $view->with('announcement', null);
            return;
        }

        $locale = app()->getLocale();
        $currency = (string) session('currency', setting('currency_code', 'USD'));
        $suffix = $locale.'_'.$currency;

        $view->with('megaMenuCategories', Schema::hasTable('categories') ? Cache::remember('mega_menu_categories_'.$suffix, 3600, fn () => Category::query()->where('is_active', true)->whereNull('parent_id')->with(['children' => fn ($q) => $q->where('is_active', true)->with('children'), 'children.children'])->orderBy('sort_order')->take(10)->get()) : collect());
        $view->with('menuItems', (Schema::hasTable('menus') && Schema::hasTable('menu_items')) ? Cache::remember('primary_menu_items_'.$suffix, 3600, fn () => MenuItem::query()->whereHas('menu', fn ($q) => $q->where('location', 'primary'))->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->with('children')])->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->get()) : collect());
        $view->with('mobileMenuItems', (Schema::hasTable('menus') && Schema::hasTable('menu_items')) ? Cache::remember('mobile_menu_items_'.$suffix, 3600, fn () => MenuItem::query()->whereHas('menu', fn ($q) => $q->whereIn('location', ['mobile', 'primary']))->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->with('children')])->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->get()) : collect());
        $view->with('currencies', Schema::hasTable('currencies') ? Cache::remember('active_currencies_'.$suffix, 3600, fn () => Currency::query()->where('is_active', true)->get()) : collect());
        $view->with('languages', Schema::hasTable('languages') ? Cache::remember('active_languages_'.$suffix, 3600, fn () => Language::query()->where('is_active', true)->get()) : collect());
        $view->with('activeFlashDeal', Schema::hasTable('flash_deals') ? Cache::remember('active_flash_deal_'.$suffix, 300, fn () => FlashDeal::query()->where('is_active', true)->where('starts_at', '<=', now())->where('ends_at', '>=', now())->first()) : null);
        $view->with('announcement', setting('announcement_text'));
    }
}

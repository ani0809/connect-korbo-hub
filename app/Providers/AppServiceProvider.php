<?php

namespace App\Providers;

use App\Models\CustomAlert;
use App\Models\DynamicPopup;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\TopBanner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
      Schema::defaultStringLength(191);
      Paginator::useBootstrap();

      View::composer('frontend.inc.nav', function ($view) {
          $topBanners = Cache::remember('top_banners_status_1_desc', now()->addMinutes(5), function () {
              return TopBanner::where('status', 1)->orderBy('id', 'desc')->get();
          });

          $view->with('topBanners', $topBanners);
      });

      View::composer('frontend.layouts.app', function ($view) {
          $alertLocation = get_setting('custom_alert_location');
          $order = in_array($alertLocation, ['top-left', 'top-right']) ? 'asc' : 'desc';

          $customAlerts = Cache::remember("custom_alerts_{$order}", now()->addMinutes(5), function () use ($order) {
              return CustomAlert::where('status', 1)->orderBy('id', $order)->get();
          });

          $dynamicPopups = Cache::remember('dynamic_popups_active_asc', now()->addMinutes(5), function () {
              return DynamicPopup::where('status', 1)->orderBy('id', 'asc')->get();
          });

          $hasUnreviewed = false;
          if (auth()->check()) {
              $reviewCheckCacheKey = 'has_unreviewed_order_details_user_' . auth()->id();
              $hasUnreviewed = Cache::remember($reviewCheckCacheKey, now()->addMinutes(2), function () {
                  $userOrderIds = Order::where('user_id', auth()->id())->pluck('id');
                  if ($userOrderIds->isEmpty()) {
                      return false;
                  }
                  return OrderDetail::whereIn('order_id', $userOrderIds)
                      ->where('delivery_status', 'delivered')
                      ->where('reviewed', 0)
                      ->exists();
              });
          }

          $view->with([
              'custom_alerts' => $customAlerts,
              'dynamic_popups' => $dynamicPopups,
              'hasUnreviewed' => $hasUnreviewed,
              'alert_order' => $order,
          ]);
      });
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //
  }
}

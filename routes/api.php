<?php

use App\Http\Controllers\Admin\BuilderApiController;
use App\Http\Controllers\Api\V1\AddressApiController;
use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\BrandApiController;
use App\Http\Controllers\Api\V1\CartApiController;
use App\Http\Controllers\Api\V1\CategoryApiController;
use App\Http\Controllers\Api\V1\CheckoutApiController;
use App\Http\Controllers\Api\V1\CompareApiController;
use App\Http\Controllers\Api\V1\DownloadApiController;
use App\Http\Controllers\Api\V1\FcmApiController;
use App\Http\Controllers\Api\V1\HomeApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use App\Http\Controllers\Api\V1\OrderApiController;
use App\Http\Controllers\Api\V1\PointsApiController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\QnaApiController;
use App\Http\Controllers\Api\V1\ReviewApiController;
use App\Http\Controllers\Api\V1\SearchApiController;
use App\Http\Controllers\Api\V1\SellerApiController;
use App\Http\Controllers\Api\V1\SettingsApiController;
use App\Http\Controllers\Api\V1\SupportApiController;
use App\Http\Controllers\Api\V1\WalletApiController;
use App\Http\Controllers\Api\V1\WishlistApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('builder')->middleware(['web', 'auth.admin'])->group(function (): void {
        Route::get('/header', [BuilderApiController::class, 'getHeader']);
        Route::get('/footer', [BuilderApiController::class, 'getFooter']);
        Route::get('/homepage', [BuilderApiController::class, 'getHomepage']);
        Route::post('/header/save', [BuilderApiController::class, 'saveHeader']);
        Route::post('/footer/save', [BuilderApiController::class, 'saveFooter']);
        Route::post('/homepage/save', [BuilderApiController::class, 'saveHomepage']);
        Route::post('/product-card/save', [BuilderApiController::class, 'saveProductCard']);
        Route::post('/product-page/save', [BuilderApiController::class, 'saveProductPage']);
        Route::post('/shop/save', [BuilderApiController::class, 'saveShop']);
        Route::post('/header/preset', [BuilderApiController::class, 'loadHeaderPreset']);
        Route::post('/footer/preset', [BuilderApiController::class, 'loadFooterPreset']);
        Route::get('/element-schema/{type}', [BuilderApiController::class, 'getElementSchema']);
        Route::post('/preview', [BuilderApiController::class, 'preview']);
    });

    Route::prefix('v1')->group(function (): void {
        Route::middleware('throttle:60,1')->group(function (): void {
            Route::post('/auth/login', [AuthApiController::class, 'login']);
            Route::post('/auth/register', [AuthApiController::class, 'register']);
            Route::post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);
            Route::post('/auth/verify-otp', [AuthApiController::class, 'verifyOtp']);
            Route::post('/auth/social-login', [AuthApiController::class, 'socialLogin']);

            Route::get('/products', [ProductApiController::class, 'index']);
            Route::get('/products/{id}/reviews', [ProductApiController::class, 'reviews'])->whereNumber('id');
            Route::get('/products/{id}/qna', [ProductApiController::class, 'qna'])->whereNumber('id');
            Route::get('/products/{slug}', [ProductApiController::class, 'show']);

            Route::get('/categories', [CategoryApiController::class, 'index']);
            Route::get('/categories/{slug}', [CategoryApiController::class, 'show']);

            Route::get('/brands', [BrandApiController::class, 'index']);

            Route::get('/search', [SearchApiController::class, 'index']);
            Route::get('/search/suggestions', [SearchApiController::class, 'suggestions']);

            Route::get('/home', [HomeApiController::class, 'index']);
            Route::get('/banners', [HomeApiController::class, 'banners']);
            Route::get('/flash-deals', [HomeApiController::class, 'flashDeals']);

            Route::get('/sellers', [SellerApiController::class, 'index']);
            Route::get('/sellers/{slug}', [SellerApiController::class, 'show']);

            Route::get('/settings', [SettingsApiController::class, 'index']);
            Route::get('/countries', [SettingsApiController::class, 'countries']);
            Route::get('/currencies', [SettingsApiController::class, 'currencies']);
            Route::get('/languages', [SettingsApiController::class, 'languages']);
        });

        Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function (): void {
            Route::post('/auth/logout', [AuthApiController::class, 'logout']);
            Route::get('/auth/user', [AuthApiController::class, 'user']);
            Route::put('/auth/profile', [AuthApiController::class, 'updateProfile']);
            Route::post('/auth/change-password', [AuthApiController::class, 'changePassword']);
            Route::post('/auth/refresh-token', [AuthApiController::class, 'refreshToken']);

            Route::post('/fcm/register', [FcmApiController::class, 'register']);
            Route::delete('/fcm/unregister', [FcmApiController::class, 'unregister']);

            Route::get('/cart', [CartApiController::class, 'index']);
            Route::post('/cart/add', [CartApiController::class, 'add']);
            Route::put('/cart/update', [CartApiController::class, 'update']);
            Route::delete('/cart/remove/{itemId}', [CartApiController::class, 'remove']);
            Route::delete('/cart/clear', [CartApiController::class, 'clear']);
            Route::post('/cart/coupon', [CartApiController::class, 'applyCoupon']);
            Route::delete('/cart/coupon', [CartApiController::class, 'removeCoupon']);

            Route::post('/checkout/shipping-methods', [CheckoutApiController::class, 'shippingMethods']);
            Route::post('/checkout/place-order', [CheckoutApiController::class, 'placeOrder']);
            Route::post('/checkout/payment-intent', [CheckoutApiController::class, 'paymentIntent']);

            Route::get('/orders', [OrderApiController::class, 'index']);
            Route::get('/orders/{number}', [OrderApiController::class, 'show']);
            Route::post('/orders/{number}/cancel', [OrderApiController::class, 'cancel']);
            Route::post('/orders/{number}/reorder', [OrderApiController::class, 'reorder']);
            Route::post('/orders/{id}/review', [OrderApiController::class, 'review'])->whereNumber('id');

            Route::get('/wishlist', [WishlistApiController::class, 'index']);
            Route::post('/wishlist/toggle', [WishlistApiController::class, 'toggle']);
            Route::get('/wishlist/ids', [WishlistApiController::class, 'ids']);

            Route::get('/addresses', [AddressApiController::class, 'index']);
            Route::post('/addresses', [AddressApiController::class, 'store']);
            Route::put('/addresses/{id}', [AddressApiController::class, 'update'])->whereNumber('id');
            Route::delete('/addresses/{id}', [AddressApiController::class, 'destroy'])->whereNumber('id');
            Route::post('/addresses/{id}/default', [AddressApiController::class, 'setDefault'])->whereNumber('id');

            Route::get('/wallet', [WalletApiController::class, 'index']);
            Route::get('/wallet/transactions', [WalletApiController::class, 'transactions']);

            Route::get('/points', [PointsApiController::class, 'balance']);
            Route::get('/points/history', [PointsApiController::class, 'history']);

            Route::get('/notifications', [NotificationApiController::class, 'index']);
            Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markRead']);
            Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllRead']);

            Route::get('/support', [SupportApiController::class, 'index']);
            Route::post('/support', [SupportApiController::class, 'store']);
            Route::get('/support/{number}', [SupportApiController::class, 'show']);
            Route::post('/support/{number}/reply', [SupportApiController::class, 'reply']);

            Route::post('/reviews', [ReviewApiController::class, 'store']);
            Route::post('/qna/questions', [QnaApiController::class, 'storeQuestion']);
            Route::post('/qna/answers', [QnaApiController::class, 'storeAnswer']);

            Route::post('/compare/toggle', [CompareApiController::class, 'toggle']);
            Route::get('/compare', [CompareApiController::class, 'index']);

            Route::get('/downloads', [DownloadApiController::class, 'index']);
            Route::get('/downloads/{id}/link', [DownloadApiController::class, 'getLink'])->whereNumber('id');
        });
    });

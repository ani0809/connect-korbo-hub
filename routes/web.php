<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductAiController as AdminProductAiController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShippingController as AdminShippingController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ThemeSettingsController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\BuilderController as AdminBuilderController;
use App\Http\Controllers\Admin\BuilderDataController as AdminBuilderDataController;
use App\Http\Controllers\Admin\AddonController as AdminAddonController;
use App\Http\Controllers\Admin\UpdateController as AdminUpdateController;
use App\Http\Controllers\Admin\PageBuilderController as AdminPageBuilderController;
use App\Http\Controllers\Admin\EmailTemplateController as AdminEmailTemplateController;
use App\Http\Controllers\Admin\SmtpController as AdminSmtpController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\SmsController as AdminSmsController;
use App\Http\Controllers\Admin\AdvancedAnalyticsController as AdminAdvancedAnalyticsController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\FlashDealController as AdminFlashDealController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Admin\SystemSettingsController as AdminSystemSettingsController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\PageController as AdminCmsPageController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\ImportExportController as AdminImportExportController;
use App\Http\Controllers\Admin\TranslationController as AdminTranslationController;
use App\Http\Controllers\Admin\ShipmentController as AdminShipmentController;
use App\Http\Controllers\Admin\FraudController as AdminFraudController;
use App\Http\Controllers\Admin\PosController as AdminPosController;
use App\Http\Controllers\Admin\PosApiController as AdminPosApiController;
use App\Http\Controllers\Admin\CourierSettingsController as AdminCourierSettingsController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\PushController as ApiPushController;
use App\Http\Controllers\Frontend\CartController as FrontendCartController;
use App\Http\Controllers\Frontend\CheckoutController as FrontendCheckoutController;
use App\Http\Controllers\Frontend\OrderTrackingController as FrontendOrderTrackingController;
use App\Http\Controllers\Frontend\CompareController as FrontendCompareController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\Frontend\HomeController as FrontendHomeController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\SellerPageController as FrontendSellerPageController;
use App\Http\Controllers\Frontend\SellerRegistrationController as FrontendSellerRegistrationController;
use App\Http\Controllers\Frontend\ShopController as FrontendShopController;
use App\Http\Controllers\Frontend\SearchController as FrontendSearchController;
use App\Http\Controllers\Frontend\CategoryController as FrontendCategoryController;
use App\Http\Controllers\Frontend\BrandController as FrontendBrandController;
use App\Http\Controllers\Frontend\BlogController as FrontendBlogController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;
use App\Http\Controllers\Frontend\BuilderPageController as FrontendBuilderPageController;
use App\Http\Controllers\Frontend\LocaleController as FrontendLocaleController;
use App\Http\Controllers\Frontend\NewsletterController as FrontendNewsletterController;
use App\Http\Controllers\Frontend\SitemapController as FrontendSitemapController;
use App\Http\Controllers\Frontend\PriceTrackerController as FrontendPriceTrackerController;
use App\Http\Controllers\Frontend\RecentlyViewedController as FrontendRecentlyViewedController;
use App\Http\Controllers\Frontend\WaitlistController as FrontendWaitlistController;
use App\Http\Controllers\Frontend\WishlistController as FrontendWishlistController;
use App\Http\Controllers\Frontend\AccountController as FrontendAccountController;
use App\Http\Controllers\Frontend\AccountOrderController as FrontendAccountOrderController;
use App\Http\Controllers\Frontend\AccountAddressController as FrontendAccountAddressController;
use App\Http\Controllers\Frontend\AccountDownloadController as FrontendAccountDownloadController;
use App\Http\Controllers\Frontend\AccountPointsController as FrontendAccountPointsController;
use App\Http\Controllers\Frontend\AccountTicketController as FrontendAccountTicketController;
use App\Http\Controllers\Frontend\AccountWalletController as FrontendAccountWalletController;
use App\Http\Controllers\Frontend\ReferralController as FrontendReferralController;
use App\Http\Controllers\Frontend\OrderLookupController as FrontendOrderLookupController;
use App\Http\Controllers\Frontend\QnaController as FrontendQnaController;
use App\Http\Controllers\Frontend\ReviewController as FrontendReviewController;
use App\Http\Controllers\Frontend\SellerReviewController as FrontendSellerReviewController;
use App\Http\Controllers\Admin\QnaController as AdminQnaController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Seller\ReviewController as SellerReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\EarningsController as SellerEarningsController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\ShopController as SellerShopController;
use App\Http\Controllers\Seller\WithdrawalController as SellerWithdrawalController;
use App\Http\Controllers\Seller\CouponController as SellerCouponController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;
use App\Http\Controllers\Admin\AccountingController as AdminAccountingController;
use App\Http\Controllers\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Admin\JournalController as AdminJournalController;
use App\Http\Controllers\Admin\BankAccountController as AdminBankAccountController;
use App\Http\Controllers\Admin\ChartOfAccountsController as AdminChartOfAccountsController;
use App\Http\Controllers\Admin\MigrationController as AdminMigrationController;
use App\Http\Controllers\Admin\TrackingSettingsController as AdminTrackingSettingsController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/__health', function (): \Illuminate\Http\JsonResponse {
    $db = ['connected' => false, 'driver' => config('database.default'), 'error' => null];
    try {
        DB::connection()->getPdo();
        $db['connected'] = true;
    } catch (\Throwable $e) {
        $db['error'] = $e->getMessage();
    }

    return response()->json([
        'ok' => true,
        'laravel' => true,
        'app_name' => config('app.name'),
        'installed_lock' => file_exists(storage_path('installed.lock')),
        'database' => $db,
        'routes_count' => count(Route::getRoutes()->getRoutes()),
    ]);
});

Route::middleware('not.installed')
    ->prefix('install')
    ->name('install.')
    ->group(function (): void {
        Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
        Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
        Route::get('/permissions', [InstallerController::class, 'permissions'])->name('permissions');
        Route::get('/database', [InstallerController::class, 'database'])->name('database');
        Route::post('/database', [InstallerController::class, 'saveDatabase'])->name('database.save');
        Route::get('/migration', [InstallerController::class, 'migration'])->name('migration');
        Route::post('/migration', [InstallerController::class, 'runMigration'])->name('migration.run');
        Route::get('/setup', [InstallerController::class, 'setup'])->name('setup');
        Route::post('/setup', [InstallerController::class, 'saveSetup'])->name('setup.save');
        Route::get('/license', [InstallerController::class, 'license'])->name('license');
        Route::post('/license', [InstallerController::class, 'activateLicense'])->name('license.activate');
        Route::get('/demo', [InstallerController::class, 'demo'])->name('demo');
        Route::post('/demo', [InstallerController::class, 'importDemo'])->name('demo.import');
        Route::get('/finish', [InstallerController::class, 'finish'])->name('finish');
    });

Route::post('/impersonate/stop', [ImpersonateController::class, 'stop'])->middleware('auth')->name('impersonate.stop');

Route::middleware(['installed', 'license'])->group(function (): void {
    Route::get('/', [FrontendHomeController::class, 'index'])->name('home');
    Route::get('/shop', [FrontendShopController::class, 'index'])->name('shop');
    Route::get('/shop/index', fn () => redirect()->route('shop'))->name('shop.index');
    Route::get('/shop/category/{slug}', [FrontendCategoryController::class, 'show'])->name('shop.category');
    Route::get('/shop/brand/{slug}', [FrontendBrandController::class, 'show'])->name('shop.brand');
    Route::get('/product/{slug}', [FrontendProductController::class, 'show'])->name('product.show');
    Route::post('/reviews/{review}/helpful', [FrontendReviewController::class, 'markHelpful'])->name('reviews.helpful');
    Route::post('/qa/questions', [FrontendQnaController::class, 'storeQuestion'])->name('qa.questions.store');
    Route::post('/qa/answers', [FrontendQnaController::class, 'storeAnswer'])->middleware('auth')->name('qa.answers.store');
    Route::get('/qa/search', [FrontendQnaController::class, 'searchQuestions'])->name('qa.search');
    Route::post('/qa/answers/{answer}/helpful', [FrontendQnaController::class, 'markAnswerHelpful'])->name('qa.answers.helpful');
    Route::post('/seller-reviews', [FrontendSellerReviewController::class, 'store'])->middleware('auth')->name('seller-reviews.store');
    Route::get('/search', [FrontendSearchController::class, 'index'])->name('search');
    Route::get('/search/suggestions', [FrontendSearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search/live', [FrontendSearchController::class, 'liveSearch'])->name('search.live');
    Route::get('/search/popular', [FrontendSearchController::class, 'popularSearches'])->name('search.popular');
    Route::get('/blog', [FrontendBlogController::class, 'index'])->name('blog');
    Route::get('/blog/category/{slug}', [FrontendBlogController::class, 'category'])->name('blog.category');
    Route::get('/blog/{slug}', [FrontendBlogController::class, 'show'])->name('blog.show');
    Route::get('/about', [FrontendPageController::class, 'about'])->name('about');
    Route::get('/contact', [FrontendPageController::class, 'contact'])->name('contact');
    Route::post('/contact', [FrontendPageController::class, 'submitContact'])->name('contact.submit');
    Route::get('/faq', [FrontendPageController::class, 'faq'])->name('faq');
    Route::get('/page/{slug}', [FrontendPageController::class, 'show'])->name('page.show');
    Route::post('/language/switch/{code}', [FrontendLocaleController::class, 'switchLanguage'])->name('language.switch');
    Route::post('/currency/switch/{code}', [FrontendLocaleController::class, 'switchCurrency'])->name('currency.switch');
    Route::post('/newsletter/subscribe', [FrontendNewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
    Route::get('/newsletter/unsubscribe/{token}', [FrontendNewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
    Route::post('/price-tracker/track', [FrontendPriceTrackerController::class, 'track'])->middleware('auth')->name('price-tracker.track');
    Route::post('/waitlist/join', [FrontendWaitlistController::class, 'join'])->name('waitlist.join');
    Route::post('/recently-viewed/track', [FrontendRecentlyViewedController::class, 'track'])->name('recently-viewed.track');
    Route::get('/recently-viewed', [FrontendRecentlyViewedController::class, 'index'])->name('recently-viewed.index');
    Route::get('/sitemap.xml', [FrontendSitemapController::class, 'index'])->name('sitemap');
    Route::get('/robots.txt', [FrontendSitemapController::class, 'robots'])->name('robots');
    Route::get('/download/file/{token}', [FrontendAccountDownloadController::class, 'secureDownload'])->name('download.file')->middleware('auth.customer');
    Route::get('/cart', [FrontendCartController::class, 'index'])->name('cart.index');
    Route::get('/checkout', [FrontendCheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/place-order', [FrontendCheckoutController::class, 'placeOrder'])->name('checkout.place');
    Route::post('/checkout/validate', [FrontendCheckoutController::class, 'validateCheckout'])->name('checkout.validate');
    Route::post('/checkout/calculate-shipping', [FrontendCheckoutController::class, 'calculateShipping'])->name('checkout.calculate-shipping');
    Route::get('/checkout/success/{orderNumber}', [FrontendCheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/compare', [FrontendCompareController::class, 'index'])->name('compare.index');
    Route::get('/track-order/{trackingCode?}', [FrontendOrderTrackingController::class, 'show'])->name('order.track');
    Route::get('/orders/track', [FrontendOrderTrackingController::class, 'track'])->name('orders.track');
    Route::match(['get', 'post'], '/orders/lookup', [FrontendOrderLookupController::class, 'lookup'])->name('orders.lookup');
    Route::get('/p/{slug}', [FrontendBuilderPageController::class, 'show'])->name('builder.page.show');
});

Route::middleware('installed')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
    Route::get('/register', function (\Illuminate\Http\Request $request): \Illuminate\View\View {
        if ($request->filled('ref')) {
            app(\App\Services\ReferralService::class)->trackVisit((string) $request->string('ref'), (string) $request->ip());
        }
        return view('auth.register');
    })->name('register');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', fn () => back()->with('status', 'Password reset link is not configured yet.'))->name('password.email');
    Route::post('/logout', fn () => redirect('/'))->name('logout');
    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::get('/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect')->where('provider', 'google|facebook');
        Route::get('/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback')->where('provider', 'google|facebook');
    });
    Route::prefix('auth/otp')->name('auth.otp.')->group(function (): void {
        Route::post('/send', [OtpAuthController::class, 'sendOtp'])->name('send')->middleware('throttle:10,1');
        Route::post('/verify-login', [OtpAuthController::class, 'verifyLogin'])->name('verify-login')->middleware('throttle:10,1');
        Route::post('/verify-register', [OtpAuthController::class, 'verifyRegister'])->name('verify-register')->middleware('throttle:5,1');
        Route::post('/reset-password', [OtpAuthController::class, 'forgotViaPhone'])->name('reset-password')->middleware('throttle:5,1');
    });
});

Route::prefix('admin')->middleware('installed')->group(function (): void {
Route::get('/', function () {
        if (Auth::check() && in_array(Auth::user()->role ?? null, ['admin', 'staff'], true)) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    })->name('admin.index');

    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('admin.logout');
});

Route::prefix('account')
    ->name('account.')
    ->middleware(['installed', 'auth.customer'])
    ->group(function (): void {
        Route::get('/', [FrontendAccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [FrontendAccountController::class, 'profile'])->name('profile');
        Route::post('/profile', [FrontendAccountController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/avatar', [FrontendAccountController::class, 'updateAvatar'])->name('profile.avatar');
        Route::post('/profile/password', [FrontendAccountController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/delete', [FrontendAccountController::class, 'deleteAccount'])->name('profile.delete');
        Route::get('/media-library/list', [\App\Http\Controllers\MediaLibraryController::class, 'index'])->name('media-library.index');
        Route::post('/media-library/upload', [\App\Http\Controllers\MediaLibraryController::class, 'upload'])->name('media-library.upload');
        Route::post('/media-library/resolve', [\App\Http\Controllers\MediaLibraryController::class, 'resolve'])->name('media-library.resolve');
        Route::post('/media-library/update', [\App\Http\Controllers\MediaLibraryController::class, 'update'])->name('media-library.update');

        Route::get('/orders', [FrontendAccountOrderController::class, 'index'])->name('orders');
        Route::get('/orders/{number}', [FrontendAccountOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{number}/cancel', [FrontendAccountOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{number}/reorder', [FrontendAccountOrderController::class, 'reorder'])->name('orders.reorder');
        Route::get('/orders/{number}/invoice', [FrontendAccountOrderController::class, 'invoice'])->name('orders.invoice');
        Route::post('/orders/{number}/repay', [FrontendAccountOrderController::class, 'repay'])->name('orders.repay');
        Route::post('/orders/{id}/review', [FrontendAccountOrderController::class, 'review'])->name('orders.review');

        Route::get('/addresses', [FrontendAccountAddressController::class, 'index'])->name('addresses');
        Route::post('/addresses', [FrontendAccountAddressController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{id}', [FrontendAccountAddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{id}', [FrontendAccountAddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/addresses/{id}/default-shipping', [FrontendAccountAddressController::class, 'setDefaultShipping'])->name('addresses.default-shipping');
        Route::post('/addresses/{id}/default-billing', [FrontendAccountAddressController::class, 'setDefaultBilling'])->name('addresses.default-billing');

        Route::get('/wishlist', [FrontendWishlistController::class, 'index'])->name('wishlist');
        Route::post('/wishlist/move-to-cart', [FrontendWishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');
        Route::post('/wishlist/move-all-to-cart', [FrontendWishlistController::class, 'moveAllToCart'])->name('wishlist.move-all-to-cart');

        Route::get('/downloads', [FrontendAccountDownloadController::class, 'index'])->name('downloads');
        Route::get('/downloads/{id}', [FrontendAccountDownloadController::class, 'download'])->name('downloads.get');
        Route::get('/download/file/{token}', [FrontendAccountDownloadController::class, 'stream'])->name('downloads.stream');

        Route::get('/points', [FrontendAccountPointsController::class, 'index'])->name('points');
        Route::get('/wallet', [FrontendAccountWalletController::class, 'index'])->name('wallet');
        Route::post('/wallet/add-money', [FrontendAccountWalletController::class, 'addMoney'])->name('wallet.add-money');
        Route::get('/referrals', [FrontendReferralController::class, 'account'])->name('referral');

        Route::get('/support', [FrontendAccountTicketController::class, 'index'])->name('support');
        Route::post('/support', [FrontendAccountTicketController::class, 'store'])->name('support.store');
        Route::get('/support/{number}', [FrontendAccountTicketController::class, 'show'])->name('support.show');
        Route::post('/support/{number}/reply', [FrontendAccountTicketController::class, 'reply'])->name('support.reply');
        Route::post('/support/{number}/close', [FrontendAccountTicketController::class, 'close'])->name('support.close');

        Route::get('/following', [FrontendAccountController::class, 'following'])->name('following');
        Route::get('/notifications', [FrontendAccountController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/mark-read', [FrontendAccountController::class, 'markRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [FrontendAccountController::class, 'markAllRead'])->name('notifications.mark-all-read');
    });

Route::middleware(['installed'])->group(function (): void {
    Route::get('/ref/{code}', [FrontendReferralController::class, 'track'])->name('referral.track');
    Route::get('/become-seller', [FrontendSellerRegistrationController::class, 'showRegistrationForm'])->name('seller.register');
    Route::post('/become-seller', [FrontendSellerRegistrationController::class, 'register'])->name('seller.register.submit');
    Route::get('/seller/pending', [FrontendSellerRegistrationController::class, 'showPendingPage'])->name('seller.registration.pending');
    Route::view('/seller/suspended', 'frontend.seller.pending')->name('seller.suspended');
    Route::get('/shop/{slug}', [FrontendSellerPageController::class, 'show'])->name('seller.shop.show');
    Route::post('/shop/follow', [FrontendSellerPageController::class, 'follow'])->middleware('auth')->name('seller.follow');
});

Route::prefix('seller')->middleware(['installed', 'auth.seller'])->group(function (): void {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('seller.dashboard');
    Route::get('/products', [SellerProductController::class, 'index'])->name('seller.products.index');
    Route::post('/products', [SellerProductController::class, 'store'])->name('seller.products.store');
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('seller.orders.index');
    Route::get('/orders/{orderNumber}', [SellerOrderController::class, 'show'])->name('seller.orders.show');
    Route::post('/orders/item-status', [SellerOrderController::class, 'updateItemStatus'])->name('seller.orders.item-status');
    Route::get('/shop/settings', [SellerShopController::class, 'settings'])->name('seller.shop.settings');
    Route::post('/shop/settings', [SellerShopController::class, 'updateSettings'])->name('seller.shop.settings.update');
    Route::get('/media-library/list', [\App\Http\Controllers\MediaLibraryController::class, 'index'])->name('seller.media-library.index');
    Route::post('/media-library/upload', [\App\Http\Controllers\MediaLibraryController::class, 'upload'])->name('seller.media-library.upload');
    Route::post('/media-library/resolve', [\App\Http\Controllers\MediaLibraryController::class, 'resolve'])->name('seller.media-library.resolve');
    Route::post('/media-library/update', [\App\Http\Controllers\MediaLibraryController::class, 'update'])->name('seller.media-library.update');
    Route::get('/earnings', [SellerEarningsController::class, 'index'])->name('seller.earnings.index');
    Route::get('/withdrawals', [SellerWithdrawalController::class, 'index'])->name('seller.withdrawal.index');
    Route::post('/withdrawals/request', [SellerWithdrawalController::class, 'request'])->name('seller.withdrawal.request');
    Route::get('/coupons', [SellerCouponController::class, 'index'])->name('seller.coupons.index');
    Route::get('/coupons/create', [SellerCouponController::class, 'create'])->name('seller.coupons.create');
    Route::post('/coupons', [SellerCouponController::class, 'store'])->name('seller.coupons.store');
    Route::get('/coupons/{id}/edit', [SellerCouponController::class, 'edit'])->name('seller.coupons.edit');
    Route::put('/coupons/{id}', [SellerCouponController::class, 'update'])->name('seller.coupons.update');
    Route::get('/reviews', [SellerReviewController::class, 'index'])->name('seller.reviews.index');
    Route::post('/reviews/{id}/reply', [SellerReviewController::class, 'reply'])->name('seller.reviews.reply');
    Route::view('/pending-approval', 'frontend.seller.pending')->name('seller.panel.pending');
});

Route::prefix('admin')->middleware(['installed', 'auth.admin', 'staff.route'])->group(function (): void {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/realtime', [AdminDashboardController::class, 'realtime'])->name('admin.dashboard.realtime');
    Route::get('/emails', [AdminEmailTemplateController::class, 'index'])->name('admin.emails.index');
    Route::get('/emails/{id}/edit', [AdminEmailTemplateController::class, 'edit'])->name('admin.emails.edit');
    Route::post('/emails/{id}', [AdminEmailTemplateController::class, 'update'])->name('admin.emails.update');
    Route::post('/emails/{id}/test', [AdminEmailTemplateController::class, 'sendTest'])->name('admin.emails.test');
    Route::get('/settings/smtp2', [AdminSmtpController::class, 'index'])->name('admin.smtp.index');
    Route::post('/settings/smtp2', [AdminSmtpController::class, 'save'])->name('admin.smtp.save');
    Route::post('/settings/smtp2/test', [AdminSmtpController::class, 'test'])->name('admin.smtp.test');
    Route::get('/settings/sms', [AdminSmsController::class, 'index'])->name('admin.sms.index');
    Route::post('/settings/sms', [AdminSmsController::class, 'save'])->name('admin.sms.save');
    Route::post('/settings/sms/test', [AdminSmsController::class, 'test'])->name('admin.sms.test');
    Route::get('/newsletter', [AdminNewsletterController::class, 'index'])->name('admin.newsletter.index');
    Route::get('/newsletter/create', [AdminNewsletterController::class, 'create'])->name('admin.newsletter.create');
    Route::post('/newsletter', [AdminNewsletterController::class, 'store'])->name('admin.newsletter.store');
    Route::post('/newsletter/{id}/send', [AdminNewsletterController::class, 'send'])->name('admin.newsletter.send');
    Route::post('/newsletter/{id}/retry', [AdminNewsletterController::class, 'retry'])->name('admin.newsletter.retry');
    Route::post('/newsletter/{id}/cancel', [AdminNewsletterController::class, 'cancel'])->name('admin.newsletter.cancel');
    Route::get('/newsletter/subscribers', [AdminNewsletterController::class, 'subscribers'])->name('admin.newsletter.subscribers');
    Route::get('/editor-media/list', [\App\Http\Controllers\Admin\EditorMediaController::class, 'index'])->name('admin.editor-media.index');
    Route::post('/editor-media/upload', [\App\Http\Controllers\Admin\EditorMediaController::class, 'upload'])->name('admin.editor-media.upload');
    Route::post('/editor-media/resolve', [\App\Http\Controllers\Admin\EditorMediaController::class, 'resolve'])->name('admin.editor-media.resolve');
    Route::get('/media-library/list', [\App\Http\Controllers\MediaLibraryController::class, 'index'])->name('admin.media-library.index');
    Route::post('/media-library/upload', [\App\Http\Controllers\MediaLibraryController::class, 'upload'])->name('admin.media-library.upload');
    Route::post('/media-library/resolve', [\App\Http\Controllers\MediaLibraryController::class, 'resolve'])->name('admin.media-library.resolve');
    Route::post('/media-library/update', [\App\Http\Controllers\MediaLibraryController::class, 'update'])->name('admin.media-library.update');
    Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('admin.reports.sales');
    Route::get('/reports/products', [AdminReportController::class, 'products'])->name('admin.reports.products');
    Route::get('/reports/customers', [AdminReportController::class, 'customers'])->name('admin.reports.customers');
    Route::get('/reports/earnings', [AdminReportController::class, 'earnings'])->name('admin.reports.earnings');
    Route::get('/reports/search-analytics', [AdminReportController::class, 'searchAnalytics'])->name('admin.reports.search-analytics');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics.index');
    Route::get('/analytics/advanced', [AdminAdvancedAnalyticsController::class, 'dashboard'])->name('admin.analytics.advanced');
    Route::get('/flash-deals', [AdminFlashDealController::class, 'index'])->name('admin.flash-deals.index');
    Route::get('/flash-deals/create', [AdminFlashDealController::class, 'create'])->name('admin.flash-deals.create');
    Route::post('/flash-deals', [AdminFlashDealController::class, 'store'])->name('admin.flash-deals.store');
    Route::get('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('admin.menus.index');
    Route::post('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'store'])->name('admin.menus.store');
    Route::post('/menus/{menu}/items', [\App\Http\Controllers\Admin\MenuController::class, 'storeItem'])->name('admin.menus.items.store');
    Route::put('/menus/items/{id}', [\App\Http\Controllers\Admin\MenuController::class, 'updateItem'])->name('admin.menus.items.update');
    Route::delete('/menus/items/{id}', [\App\Http\Controllers\Admin\MenuController::class, 'destroyItem'])->name('admin.menus.items.destroy');
    Route::post('/menus/reorder', [\App\Http\Controllers\Admin\MenuController::class, 'reorder'])->name('admin.menus.reorder');

    Route::get('/pages', [AdminCmsPageController::class, 'index'])->name('admin.pages.index');
    Route::get('/pages/create', [AdminCmsPageController::class, 'create'])->name('admin.pages.create');
    Route::post('/pages/reorder', [AdminCmsPageController::class, 'reorder'])->name('admin.pages.reorder');
    Route::post('/pages', [AdminCmsPageController::class, 'store'])->name('admin.pages.store');
    Route::get('/pages/{id}/edit', [AdminCmsPageController::class, 'edit'])->name('admin.pages.edit');
    Route::put('/pages/{id}', [AdminCmsPageController::class, 'update'])->name('admin.pages.update');
    Route::delete('/pages/{id}', [AdminCmsPageController::class, 'destroy'])->name('admin.pages.destroy');

    Route::get('/faq-manage', [AdminFaqController::class, 'index'])->name('admin.faq.index');
    Route::post('/faq-manage', [AdminFaqController::class, 'store'])->name('admin.faq.store');
    Route::put('/faq-manage/{id}', [AdminFaqController::class, 'update'])->name('admin.faq.update');
    Route::delete('/faq-manage/{id}', [AdminFaqController::class, 'destroy'])->name('admin.faq.destroy');
    Route::post('/faq-manage/reorder', [AdminFaqController::class, 'reorder'])->name('admin.faq.reorder');

    Route::get('/contact-inquiries', [AdminContactController::class, 'index'])->name('admin.contact.index');
    Route::get('/contact-inquiries/{id}', [AdminContactController::class, 'show'])->name('admin.contact.show');
    Route::post('/contact-inquiries/{id}/reply', [AdminContactController::class, 'reply'])->name('admin.contact.reply');

    Route::get('/tools/import-export', [AdminImportExportController::class, 'productImportPage'])->name('admin.import-export.products');
    Route::post('/tools/import-export/products', [AdminImportExportController::class, 'importProducts'])->name('admin.import-export.products.import');
    Route::get('/tools/import-export/status', [AdminImportExportController::class, 'importStatus'])->name('admin.import-export.products.status');
    Route::get('/tools/import-export/template', [AdminImportExportController::class, 'downloadTemplate'])->name('admin.import-export.products.template');
    Route::post('/tools/import-export/products/export', [AdminImportExportController::class, 'exportProducts'])->name('admin.import-export.products.export');
    Route::post('/tools/import-export/orders', [AdminImportExportController::class, 'exportOrders'])->name('admin.import-export.orders.export');
    Route::post('/tools/import-export/customers', [AdminImportExportController::class, 'exportCustomers'])->name('admin.import-export.customers.export');

    Route::get('/translations', [AdminTranslationController::class, 'index'])->name('admin.translations.index');
    Route::get('/translations/ui/{locale}', [AdminTranslationController::class, 'uiStrings'])->name('admin.translations.ui');
    Route::post('/translations/ui/{locale}', [AdminTranslationController::class, 'saveUiStrings'])->name('admin.translations.ui.save');
    Route::post('/translations/ui/{locale}/auto', [AdminTranslationController::class, 'autoTranslateAll'])->name('admin.translations.ui.auto');
    Route::get('/translations/ui/{locale}/export', [AdminTranslationController::class, 'exportJson'])->name('admin.translations.ui.export');
    Route::post('/translations/ui/{locale}/import', [AdminTranslationController::class, 'importJson'])->name('admin.translations.ui.import');

    Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{id}/edit', [AdminProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::post('/products/bulk-action', [AdminProductController::class, 'bulkAction'])->name('admin.products.bulk-action');
    Route::post('/products/bulk-import', [AdminProductController::class, 'bulkImport'])->name('admin.products.bulk-import');
    Route::get('/products/export', [AdminProductController::class, 'exportProducts'])->name('admin.products.export');
    Route::post('/products/generate-sku', [AdminProductController::class, 'generateSku'])->name('admin.products.generate-sku');
    Route::post('/products/ai-generate', [AdminProductAiController::class, 'generate'])->name('admin.products.ai-generate');
    Route::view('/categories', 'admin.products.categories')->name('admin.categories.index');
    Route::view('/brands', 'admin.products.brands')->name('admin.brands.index');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/orders/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::post('/orders/item-status', [AdminOrderController::class, 'updateItemStatus'])->name('admin.orders.item-status');
    Route::get('/orders/{id}/invoice', [AdminOrderController::class, 'generateInvoice'])->name('admin.orders.invoice');
    Route::get('/orders/export', [AdminOrderController::class, 'bulkExport'])->name('admin.orders.export');
    Route::get('/sellers', [\App\Http\Controllers\Admin\SellerController::class, 'index'])->name('admin.sellers.index');
    Route::get('/sellers/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'show'])->name('admin.sellers.show');
    Route::get('/sellers/{id}/approve', [\App\Http\Controllers\Admin\SellerController::class, 'approve'])->name('admin.sellers.approve');
    Route::get('/sellers/{id}/suspend', [\App\Http\Controllers\Admin\SellerController::class, 'suspend'])->name('admin.sellers.suspend');
    Route::post('/sellers/followers', [\App\Http\Controllers\Admin\SellerController::class, 'addCustomFollowers'])->name('admin.sellers.followers');

    Route::get('/staff', [AdminStaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/staff/create', [AdminStaffController::class, 'create'])->name('admin.staff.create');
    Route::post('/staff', [AdminStaffController::class, 'store'])->name('admin.staff.store');
    Route::get('/staff/{id}/edit', [AdminStaffController::class, 'edit'])->name('admin.staff.edit');
    Route::put('/staff/{id}', [AdminStaffController::class, 'update'])->name('admin.staff.update');
    Route::post('/staff/{id}/toggle', [AdminStaffController::class, 'toggleStatus'])->name('admin.staff.toggle-status');
    Route::delete('/staff/{id}', [AdminStaffController::class, 'destroy'])->name('admin.staff.destroy');
    Route::get('/staff/{id}/activity', [AdminStaffController::class, 'activityLog'])->name('admin.staff.activity');

    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/customers/export', [AdminCustomerController::class, 'export'])->name('admin.customers.export');
    Route::get('/customers/create', [AdminCustomerController::class, 'create'])->name('admin.customers.create');
    Route::post('/customers', [AdminCustomerController::class, 'store'])->name('admin.customers.store');
    Route::get('/customers/{id}', [AdminCustomerController::class, 'show'])->name('admin.customers.show');
    Route::get('/customers/{id}/edit', [AdminCustomerController::class, 'edit'])->name('admin.customers.edit');
    Route::put('/customers/{id}', [AdminCustomerController::class, 'update'])->name('admin.customers.update');
    Route::post('/customers/{id}/toggle', [AdminCustomerController::class, 'toggleStatus'])->name('admin.customers.toggle-status');
    Route::post('/customers/{id}/wallet', [AdminCustomerController::class, 'adjustWallet'])->name('admin.customers.wallet');
    Route::post('/customers/{id}/points', [AdminCustomerController::class, 'adjustPoints'])->name('admin.customers.points');
    Route::post('/customers/{id}/login-as', [AdminCustomerController::class, 'loginAsCustomer'])->name('admin.customers.login-as');

    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('admin.coupons.index');
    Route::get('/coupons/create', [AdminCouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('admin.coupons.store');
    Route::get('/coupons/{id}/edit', [AdminCouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::put('/coupons/{id}', [AdminCouponController::class, 'update'])->name('admin.coupons.update');
    Route::post('/coupons/{id}/toggle', [AdminCouponController::class, 'toggleStatus'])->name('admin.coupons.toggle');
    Route::get('/coupons/{id}/stats', [AdminCouponController::class, 'usageStats'])->name('admin.coupons.stats');
    Route::get('/coupons/import-template', [AdminCouponController::class, 'downloadImportTemplate'])->name('admin.coupons.import-template');
    Route::get('/coupons/generate-code', [AdminCouponController::class, 'generateCode'])->name('admin.coupons.generate-code');
    Route::post('/coupons/bulk-generate', [AdminCouponController::class, 'bulkGenerate'])->name('admin.coupons.bulk-generate');
    Route::post('/coupons/import', [AdminCouponController::class, 'importCoupons'])->name('admin.coupons.import');
    Route::delete('/coupons/{id}', [AdminCouponController::class, 'destroy'])->name('admin.coupons.destroy');
    Route::get('/promotions', [AdminPromotionController::class, 'index'])->name('admin.promotions.index');
    Route::get('/promotions/create', [AdminPromotionController::class, 'create'])->name('admin.promotions.create');
    Route::post('/promotions', [AdminPromotionController::class, 'store'])->name('admin.promotions.store');
    Route::get('/promotions/{id}/edit', [AdminPromotionController::class, 'edit'])->name('admin.promotions.edit');
    Route::put('/promotions/{id}', [AdminPromotionController::class, 'update'])->name('admin.promotions.update');
    Route::post('/promotions/{id}/duplicate', [AdminPromotionController::class, 'duplicate'])->name('admin.promotions.duplicate');
    Route::delete('/promotions/{id}', [AdminPromotionController::class, 'destroy'])->name('admin.promotions.destroy');
    Route::get('/referrals', [AdminReferralController::class, 'index'])->name('admin.referrals.index');
    Route::get('/shipping', fn () => redirect()->route('admin.shipping.method'))->name('admin.shipping.index');
    Route::get('/shipping/method', [AdminShippingController::class, 'shippingMethod'])->name('admin.shipping.method');
    Route::post('/shipping/method', [AdminShippingController::class, 'saveShippingMethod'])->name('admin.shipping.method.save');
    Route::get('/shipping/areas', [AdminShippingController::class, 'areas'])->name('admin.shipping.areas');
    Route::get('/shipping/carriers', [AdminShippingController::class, 'carriers'])->name('admin.shipping.carriers');
    Route::get('/shipments', [AdminShipmentController::class, 'index'])->name('admin.shipments.index');
    Route::get('/shipments/create/{orderId}', [AdminShipmentController::class, 'create'])->name('admin.shipments.create');
    Route::post('/shipments/create/{orderId}', [AdminShipmentController::class, 'store'])->name('admin.shipments.store');
    Route::post('/shipments/bulk-create', [AdminShipmentController::class, 'bulkCreate'])->name('admin.shipments.bulk-create');
    Route::post('/shipments/{id}/track', [AdminShipmentController::class, 'track'])->name('admin.shipments.track');
    Route::get('/shipments/{id}/label', [AdminShipmentController::class, 'printLabel'])->name('admin.shipments.label');
    Route::post('/shipments/{id}/cancel', [AdminShipmentController::class, 'cancel'])->name('admin.shipments.cancel');
    Route::get('/fraud', [AdminFraudController::class, 'dashboard'])->name('admin.fraud.dashboard');
    Route::post('/fraud/blacklist', [AdminFraudController::class, 'addToBlacklist'])->name('admin.fraud.blacklist.add');
    Route::post('/fraud/blacklist/remove', [AdminFraudController::class, 'removeFromBlacklist'])->name('admin.fraud.blacklist.remove');
    Route::post('/fraud/{id}/clear', [AdminFraudController::class, 'clearOrder'])->name('admin.fraud.clear');
    Route::post('/fraud/{id}/block', [AdminFraudController::class, 'blockOrder'])->name('admin.fraud.block');
    Route::post('/fraud/settings', [AdminFraudController::class, 'saveSettings'])->name('admin.fraud.settings.save');

    Route::prefix('pos')->group(function (): void {
        Route::get('/', [AdminPosController::class, 'index'])->name('admin.pos.index');
        Route::get('/open', [AdminPosController::class, 'openSession'])->name('admin.pos.open');
        Route::post('/start', [AdminPosController::class, 'startSession'])->name('admin.pos.start');
        Route::get('/session/{session}/report', [AdminPosController::class, 'sessionReport'])->name('admin.pos.session.report');
        Route::get('/{session}/terminal', [AdminPosController::class, 'terminal'])->name('admin.pos.terminal');
        Route::post('/{session}/close', [AdminPosController::class, 'closeSession'])->name('admin.pos.close');

        Route::prefix('api')->name('admin.pos.api.')->group(function (): void {
            Route::get('/products', [AdminPosApiController::class, 'searchProducts'])->name('products');
            Route::get('/customers', [AdminPosApiController::class, 'searchCustomers'])->name('customers');
            Route::post('/place-order', [AdminPosApiController::class, 'placeOrder'])->name('place-order');
            Route::post('/cash-in-out', [AdminPosApiController::class, 'cashInOut'])->name('cash-in-out');
            Route::get('/receipt/{order}', [AdminPosApiController::class, 'receipt'])->name('receipt');
        });
    });

    Route::prefix('accounting')->name('admin.accounting.')->group(function (): void {
        Route::get('/', [AdminAccountingController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports/profit-loss', [AdminAccountingController::class, 'profitAndLoss'])->name('reports.profit-loss');
        Route::get('/reports/balance-sheet', [AdminAccountingController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::get('/reports/trial-balance', [AdminAccountingController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('/reports/cash-flow', [AdminAccountingController::class, 'cashFlow'])->name('reports.cash-flow');
        Route::get('/reports/tax', [AdminAccountingController::class, 'taxReport'])->name('reports.tax');

        Route::get('/expenses', [AdminExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [AdminExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [AdminExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{id}/edit', [AdminExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{id}', [AdminExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{id}', [AdminExpenseController::class, 'destroy'])->name('expenses.destroy');

        Route::get('/journals', [AdminJournalController::class, 'index'])->name('journals.index');
        Route::get('/journals/create', [AdminJournalController::class, 'create'])->name('journals.create');
        Route::post('/journals', [AdminJournalController::class, 'store'])->name('journals.store');
        Route::post('/journals/{id}/void', [AdminJournalController::class, 'void'])->name('journals.void');

        Route::get('/bank-accounts', [AdminBankAccountController::class, 'index'])->name('bank-accounts.index');
        Route::post('/bank-accounts', [AdminBankAccountController::class, 'store'])->name('bank-accounts.store');
        Route::post('/bank-accounts/{id}/balance', [AdminBankAccountController::class, 'updateBalance'])->name('bank-accounts.balance');

        Route::get('/chart-of-accounts', [AdminChartOfAccountsController::class, 'index'])->name('chart-of-accounts.index');
        Route::post('/chart-of-accounts', [AdminChartOfAccountsController::class, 'store'])->name('chart-of-accounts.store');
        Route::put('/chart-of-accounts/{id}', [AdminChartOfAccountsController::class, 'update'])->name('chart-of-accounts.update');
        Route::delete('/chart-of-accounts/{id}', [AdminChartOfAccountsController::class, 'destroy'])->name('chart-of-accounts.destroy');
    });

    Route::prefix('migration')->name('admin.migration.')->group(function (): void {
        Route::get('/', [AdminMigrationController::class, 'index'])->name('index');
        Route::get('/wizard', [AdminMigrationController::class, 'wizard'])->name('wizard');
        Route::post('/test-connection', [AdminMigrationController::class, 'testConnection'])->name('test-connection');
        Route::post('/start', [AdminMigrationController::class, 'startMigration'])->name('start');
        Route::get('/progress/{id}', [AdminMigrationController::class, 'progress'])->name('progress');
    });

    Route::get('/payment', [AdminPaymentController::class, 'index'])->name('admin.payment.index');
    Route::put('/payment/{slug}', [AdminPaymentController::class, 'update'])->name('admin.payments.update');
    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('admin.withdrawals.index');
    Route::post('/withdrawals/process', [AdminWithdrawalController::class, 'process'])->name('admin.withdrawals.process');
    Route::post('/withdrawals/reject', [AdminWithdrawalController::class, 'reject'])->name('admin.withdrawals.reject');
    Route::get('/commission', [AdminCommissionController::class, 'index'])->name('admin.commission.index');
    Route::post('/commission', [AdminCommissionController::class, 'save'])->name('admin.commission.save');
    Route::get('/settings/general', [AdminSettingController::class, 'general'])->name('admin.settings.general');
    Route::post('/settings/general', [AdminSettingController::class, 'saveGeneral'])->name('admin.settings.general.save');
    Route::get('/settings/business', [AdminSettingController::class, 'business'])->name('admin.settings.business');
    Route::post('/settings/business', [AdminSettingController::class, 'saveBusiness'])->name('admin.settings.business.save');
    Route::get('/settings/smtp', [AdminSettingController::class, 'smtp'])->name('admin.settings.smtp');
    Route::post('/settings/smtp', [AdminSettingController::class, 'saveSmtp'])->name('admin.settings.smtp.save');
    Route::post('/settings/smtp/test', [AdminSettingController::class, 'testSmtp'])->name('admin.settings.smtp.test');
    Route::get('/settings/social-login', [AdminSettingController::class, 'socialLogin'])->name('admin.settings.social-login');
    Route::post('/settings/social-login', [AdminSettingController::class, 'saveSocialLogin'])->name('admin.settings.social-login.save');
    Route::get('/settings/social-auth', [AdminSystemSettingsController::class, 'socialLogin'])->name('admin.settings.social-auth');
    Route::post('/settings/social-auth', [AdminSystemSettingsController::class, 'saveSocialLogin'])->name('admin.settings.social-auth.save');
    Route::get('/settings/promotions', [AdminSystemSettingsController::class, 'promotions'])->name('admin.settings.promotions');
    Route::post('/settings/promotions', [AdminSystemSettingsController::class, 'savePromotions'])->name('admin.settings.promotions.save');
    Route::get('/settings/pos', [AdminSystemSettingsController::class, 'pos'])->name('admin.settings.pos');
    Route::post('/settings/pos', [AdminSystemSettingsController::class, 'savePos'])->name('admin.settings.pos.save');
    Route::get('/settings/seo', [AdminSettingController::class, 'seo'])->name('admin.settings.seo');
    Route::post('/settings/seo', [AdminSettingController::class, 'saveSeo'])->name('admin.settings.seo.save');
    Route::get('/settings/license', [AdminSettingController::class, 'license'])->name('admin.settings.license');
    Route::post('/settings/license/reactivate', [AdminSettingController::class, 'reactivateLicense'])->name('admin.settings.license.reactivate');
    Route::get('/settings/reviews', [AdminSettingController::class, 'reviews'])->name('admin.settings.reviews');
    Route::post('/settings/reviews', [AdminSettingController::class, 'saveReviews'])->name('admin.settings.reviews.save');
    Route::get('/settings/points', [AdminSettingController::class, 'points'])->name('admin.settings.points');
    Route::post('/settings/points', [AdminSettingController::class, 'savePoints'])->name('admin.settings.points.save');
    Route::get('/settings/whatsapp', [AdminSettingController::class, 'whatsapp'])->name('admin.settings.whatsapp');
    Route::post('/settings/whatsapp', [AdminSettingController::class, 'saveWhatsapp'])->name('admin.settings.whatsapp.save');
    Route::post('/settings/whatsapp/test-api', [AdminSettingController::class, 'testWhatsappApi'])->name('admin.settings.whatsapp.test');
    Route::get('/settings/courier', [AdminCourierSettingsController::class, 'index'])->name('admin.settings.courier');
    Route::post('/settings/courier', [AdminCourierSettingsController::class, 'save'])->name('admin.settings.courier.save');
    Route::post('/settings/courier/test/{courier}', [AdminCourierSettingsController::class, 'testConnection'])->name('admin.settings.courier.test');
    Route::post('/settings/courier/steadfast-balance', [AdminCourierSettingsController::class, 'steadfastBalance'])->name('admin.settings.courier.steadfast-balance');
    Route::get('/settings/tracking', [AdminTrackingSettingsController::class, 'index'])->name('admin.settings.tracking');
    Route::post('/settings/tracking', [AdminTrackingSettingsController::class, 'save'])->name('admin.settings.tracking.save');
    Route::get('/settings/system', [AdminSystemSettingsController::class, 'system'])->name('admin.settings.system');
    Route::post('/settings/system', [AdminSystemSettingsController::class, 'saveSystem'])->name('admin.settings.system.save');
    Route::get('/settings/order', [AdminSystemSettingsController::class, 'order'])->name('admin.settings.order');
    Route::post('/settings/order', [AdminSystemSettingsController::class, 'saveOrder'])->name('admin.settings.order.save');
    Route::get('/settings/tax', [AdminSystemSettingsController::class, 'tax'])->name('admin.settings.tax');
    Route::post('/settings/tax', [AdminSystemSettingsController::class, 'saveTax'])->name('admin.settings.tax.save');
    Route::get('/settings/customer', [AdminSystemSettingsController::class, 'customer'])->name('admin.settings.customer');
    Route::post('/settings/customer', [AdminSystemSettingsController::class, 'saveCustomer'])->name('admin.settings.customer.save');
    Route::get('/settings/seller', [AdminSystemSettingsController::class, 'seller'])->name('admin.settings.seller');
    Route::post('/settings/seller', [AdminSystemSettingsController::class, 'saveSeller'])->name('admin.settings.seller.save');
    Route::get('/settings/units', [AdminSystemSettingsController::class, 'units'])->name('admin.settings.units');
    Route::post('/settings/units', [AdminSystemSettingsController::class, 'saveUnits'])->name('admin.settings.units.save');
    Route::get('/settings/cookie', [AdminSystemSettingsController::class, 'cookie'])->name('admin.settings.cookie');
    Route::post('/settings/cookie', [AdminSystemSettingsController::class, 'saveCookie'])->name('admin.settings.cookie.save');
    Route::get('/settings/maintenance', [AdminSystemSettingsController::class, 'maintenance'])->name('admin.settings.maintenance');
    Route::post('/settings/maintenance', [AdminSystemSettingsController::class, 'saveMaintenance'])->name('admin.settings.maintenance.save');
    Route::get('/settings/backup', [AdminSystemSettingsController::class, 'backup'])->name('admin.settings.backup');
    Route::post('/settings/backup', [AdminSystemSettingsController::class, 'saveBackup'])->name('admin.settings.backup.save');
    Route::post('/settings/backup/run', [AdminSystemSettingsController::class, 'runBackup'])->name('admin.settings.backup.run');
    Route::view('/settings', 'admin.settings.index')->name('admin.settings.index');
    Route::get('/theme-settings', [ThemeSettingsController::class, 'index'])->name('admin.theme-settings');
    Route::post('/theme-settings/save', [ThemeSettingsController::class, 'save'])->name('admin.theme-settings.save');
    Route::post('/theme-settings/reset', [ThemeSettingsController::class, 'reset'])->name('admin.theme-settings.reset');
    Route::post('/theme-settings/export', [ThemeSettingsController::class, 'export'])->name('admin.theme-settings.export');
    Route::post('/theme-settings/import', [ThemeSettingsController::class, 'import'])->name('admin.theme-settings.import');
    Route::view('/builder', 'admin.builder.homepage')->name('admin.layout-builder.index');
    Route::get('/builder/header', [AdminBuilderController::class, 'header'])->name('admin.builder.header');
    Route::get('/builder/footer', [AdminBuilderController::class, 'footer'])->name('admin.builder.footer');
    Route::get('/builder/homepage', [AdminBuilderController::class, 'homepage'])->name('admin.builder.homepage');
    Route::get('/builder/product-card', [AdminBuilderController::class, 'productCard'])->name('admin.builder.product-card');
    Route::get('/builder/product-page', [AdminBuilderController::class, 'productPage'])->name('admin.builder.product-page');
    Route::get('/builder/shop', [AdminBuilderController::class, 'shop'])->name('admin.builder.shop');
    Route::get('/addons', [AdminAddonController::class, 'index'])->name('admin.addons.index');
    Route::post('/addons/install', [AdminAddonController::class, 'install'])->name('admin.addons.install');
    Route::post('/addons/upload-install', [AdminAddonController::class, 'uploadInstall'])->name('admin.addons.upload-install');
    Route::post('/addons/activate', [AdminAddonController::class, 'activate'])->name('admin.addons.activate');
    Route::post('/addons/deactivate', [AdminAddonController::class, 'deactivate'])->name('admin.addons.deactivate');
    Route::post('/addons/uninstall', [AdminAddonController::class, 'uninstall'])->name('admin.addons.uninstall');
    Route::get('/addons/{slug}/settings', [AdminAddonController::class, 'settings'])->name('admin.addons.settings');

    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('admin.reviews.index');
    Route::post('/reviews/{id}/approve', [AdminReviewController::class, 'approve'])->name('admin.reviews.approve');
    Route::post('/reviews/{id}/reject', [AdminReviewController::class, 'reject'])->name('admin.reviews.reject');
    Route::post('/reviews/bulk', [AdminReviewController::class, 'bulkAction'])->name('admin.reviews.bulk');
    Route::post('/reviews/{id}/reply', [AdminReviewController::class, 'reply'])->name('admin.reviews.reply');
    Route::post('/reviews/custom', [AdminReviewController::class, 'addCustomReview'])->name('admin.reviews.custom');
    Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    Route::get('/features', [AdminFeatureController::class, 'index'])->name('admin.features.index');
    Route::post('/features', [AdminFeatureController::class, 'save'])->name('admin.features.save');

    Route::get('/qna', [AdminQnaController::class, 'index'])->name('admin.qna.index');
    Route::post('/qna/questions/{id}/approve', [AdminQnaController::class, 'approveQuestion'])->name('admin.qna.questions.approve');
    Route::post('/qna/questions/{id}/reject', [AdminQnaController::class, 'rejectQuestion'])->name('admin.qna.questions.reject');
    Route::post('/qna/answers/{id}/approve', [AdminQnaController::class, 'approveAnswer'])->name('admin.qna.answers.approve');
    Route::delete('/qna/questions/{id}', [AdminQnaController::class, 'deleteQuestion'])->name('admin.qna.questions.destroy');
    Route::delete('/qna/answers/{id}', [AdminQnaController::class, 'deleteAnswer'])->name('admin.qna.answers.destroy');
    Route::post('/qna/admin-answer', [AdminQnaController::class, 'addAdminAnswer'])->name('admin.qna.admin-answer');
    Route::get('/update', [AdminUpdateController::class, 'index'])->name('admin.update.index');
    Route::post('/update/check', [AdminUpdateController::class, 'checkForUpdate'])->name('admin.update.check');
    Route::get('/update/download', [AdminUpdateController::class, 'download'])->name('admin.update.download');
    Route::get('/update/apply', [AdminUpdateController::class, 'applyUpdate'])->name('admin.update.apply');
    Route::post('/update/upload', [AdminUpdateController::class, 'uploadUpdate'])->name('admin.update.upload');
});

Route::prefix('admin/page-builder')
    ->name('admin.builder.')
    ->middleware(['installed', 'auth.admin'])
    ->group(function (): void {
        Route::get('/', [AdminPageBuilderController::class, 'index'])->name('index');
        Route::get('/create', [AdminPageBuilderController::class, 'create'])->name('create');
        Route::post('/create', [AdminPageBuilderController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminPageBuilderController::class, 'edit'])->name('edit');
        Route::put('/{id}/settings', [AdminPageBuilderController::class, 'updateSettings'])->name('settings.update');
        Route::delete('/{id}', [AdminPageBuilderController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [AdminPageBuilderController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/publish', [AdminPageBuilderController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [AdminPageBuilderController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/set-homepage', [AdminPageBuilderController::class, 'setHomepage'])->name('set-homepage');
        Route::get('/templates', [AdminPageBuilderController::class, 'templates'])->name('templates');
        Route::post('/templates/{id}/apply/{pageId}', [AdminPageBuilderController::class, 'applyTemplate'])->name('templates.apply');
    });

Route::prefix('api/builder')
    ->name('api.builder.')
    ->middleware(['installed', 'auth.admin'])
    ->group(function (): void {
        Route::post('/save/{id}', [\App\Http\Controllers\Admin\BuilderApiController::class, 'save'])->name('save');
        Route::get('/load/{id}', [\App\Http\Controllers\Admin\BuilderApiController::class, 'load'])->name('load');
        Route::post('/upload-image', [\App\Http\Controllers\Admin\BuilderApiController::class, 'uploadImage'])->name('upload-image');
        Route::get('/data/products', [AdminBuilderDataController::class, 'products'])->name('data.products');
        Route::get('/data/categories', [AdminBuilderDataController::class, 'categories'])->name('data.categories');
        Route::get('/data/sellers', [AdminBuilderDataController::class, 'sellers'])->name('data.sellers');
        Route::get('/data/flash-deals', [AdminBuilderDataController::class, 'flashDeals'])->name('data.flash-deals');
        Route::get('/data/blog-posts', [AdminBuilderDataController::class, 'blogPosts'])->name('data.blog-posts');
        Route::post('/preview/{id}', [\App\Http\Controllers\Admin\BuilderApiController::class, 'preview'])->name('preview');
        Route::get('/export/{id}', [\App\Http\Controllers\Admin\BuilderApiController::class, 'export'])->name('export');
        Route::post('/import', [\App\Http\Controllers\Admin\BuilderApiController::class, 'import'])->name('import');
    });

Route::prefix('api')->middleware('installed')->group(function (): void {
    Route::get('/product/{id}/quick-view', [FrontendProductController::class, 'quickView'])->name('api.product.quick-view');
    Route::post('/cart/add', [FrontendCartController::class, 'add'])->name('api.cart.add');
    Route::put('/cart/update', [FrontendCartController::class, 'update'])->name('api.cart.update');
    Route::delete('/cart/remove', [FrontendCartController::class, 'remove'])->name('api.cart.remove');
    Route::post('/cart/coupon', [FrontendCartController::class, 'applyCoupon'])->name('api.cart.coupon.apply');
    Route::delete('/cart/coupon', [FrontendCartController::class, 'removeCoupon'])->name('api.cart.coupon.remove');
    Route::get('/cart/mini', [FrontendCartController::class, 'miniCart'])->name('api.cart.mini');
    Route::post('/wishlist/toggle', [FrontendWishlistController::class, 'toggle'])->middleware('auth')->name('api.wishlist.toggle');
    Route::get('/wishlist/ids', [FrontendWishlistController::class, 'ids'])->middleware('auth')->name('api.wishlist.ids');
    Route::post('/compare/toggle', [FrontendCompareController::class, 'toggle'])->name('api.compare.toggle');
    Route::post('/compare/clear', [FrontendCompareController::class, 'clear'])->name('api.compare.clear');
    Route::get('/products/filter', fn () => response()->json(['items' => []]))->name('api.products.filter');
    Route::prefix('notifications')->middleware('auth')->group(function (): void {
        Route::get('/', [NotificationApiController::class, 'index']);
        Route::post('/{id}/read', [NotificationApiController::class, 'markRead']);
        Route::post('/mark-all-read', [NotificationApiController::class, 'markAllRead']);
        Route::delete('/{id}', [NotificationApiController::class, 'destroy']);
    });
    Route::post('/push/register', [ApiPushController::class, 'register'])->middleware('auth');
});

Route::prefix('payment')->name('payment.')->group(function (): void {
    Route::post('/stripe/intent', [PaymentController::class, 'stripeIntent'])->name('stripe.intent');
    Route::post('/stripe/webhook', [PaymentController::class, 'stripeWebhook'])->name('stripe.webhook')->withoutMiddleware([VerifyCsrfToken::class]);

    Route::get('/paypal/redirect/{order}', [PaymentController::class, 'paypalRedirect'])->name('paypal.redirect');
    Route::get('/paypal/success', [PaymentController::class, 'paypalSuccess'])->name('paypal.success');
    Route::get('/paypal/cancel', [PaymentController::class, 'paypalCancel'])->name('paypal.cancel');

    Route::get('/bkash/redirect/{order}', [PaymentController::class, 'bkashRedirect'])->name('bkash.redirect');
    Route::post('/bkash/callback', [PaymentController::class, 'bkashCallback'])->name('bkash.callback')->withoutMiddleware([VerifyCsrfToken::class]);

    Route::get('/nagad/redirect/{order}', [PaymentController::class, 'nagadRedirect'])->name('nagad.redirect');
    Route::post('/nagad/callback', [PaymentController::class, 'nagadCallback'])->name('nagad.callback')->withoutMiddleware([VerifyCsrfToken::class]);

    Route::post('/sslcommerz/redirect/{order}', [PaymentController::class, 'sslcommerzRedirect'])->name('sslcommerz.redirect');
    Route::post('/sslcommerz/success', [PaymentController::class, 'sslcommerzSuccess'])->name('sslcommerz.success')->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('/sslcommerz/fail', [PaymentController::class, 'sslcommerzFail'])->name('sslcommerz.fail')->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('/sslcommerz/cancel', [PaymentController::class, 'sslcommerzCancel'])->name('sslcommerz.cancel')->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('/sslcommerz/ipn', [PaymentController::class, 'sslcommerzIpn'])->name('sslcommerz.ipn')->withoutMiddleware([VerifyCsrfToken::class]);

    Route::get('/callback/{gateway}', [PaymentController::class, 'genericCallback'])->name('callback');
    Route::get('/failed/{order}', [PaymentController::class, 'failed'])->name('failed');
});

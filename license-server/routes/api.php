<?php

use App\Http\Controllers\Api\LicenseApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1', 'api.secret'])->group(function (): void {
    Route::post('/license/verify', [LicenseApiController::class, 'verify']);
    Route::post('/license/activate', [LicenseApiController::class, 'activate']);
    Route::post('/license/deactivate', [LicenseApiController::class, 'deactivate']);
    Route::post('/license/ping', [LicenseApiController::class, 'ping']);
    Route::get('/license/info', [LicenseApiController::class, 'info']);

    Route::post('/addon/verify', [LicenseApiController::class, 'addonVerify']);

    Route::get('/update/check', [LicenseApiController::class, 'checkUpdate']);
    Route::get('/update/download/{version}', [LicenseApiController::class, 'downloadUpdate']);
    Route::post('/update/download/{version}', [LicenseApiController::class, 'downloadUpdate']);
});

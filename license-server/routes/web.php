<?php

use App\Http\Controllers\Admin\AddonLicenseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\UpdateReleaseController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
    Route::get('/licenses/create', [LicenseController::class, 'create'])->name('licenses.create');
    Route::post('/licenses', [LicenseController::class, 'store'])->name('licenses.store');
    Route::get('/licenses/{license}', [LicenseController::class, 'show'])->name('licenses.show');
    Route::patch('/licenses/{license}', [LicenseController::class, 'update'])->name('licenses.update');

    Route::get('/addon-licenses', [AddonLicenseController::class, 'index'])->name('addons.index');
    Route::get('/addon-licenses/create', [AddonLicenseController::class, 'create'])->name('addons.create');
    Route::post('/addon-licenses', [AddonLicenseController::class, 'store'])->name('addons.store');

    Route::get('/releases', [UpdateReleaseController::class, 'index'])->name('releases.index');
    Route::get('/releases/create', [UpdateReleaseController::class, 'create'])->name('releases.create');
    Route::post('/releases', [UpdateReleaseController::class, 'store'])->name('releases.store');
});

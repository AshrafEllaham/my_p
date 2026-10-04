<?php

use App\Http\Controllers\Api\Store\StoreProfileController;
use Illuminate\Support\Facades\Route;

/**
 * مسارات API للمستخدم — البادئة: /api/store
 */
Route::group([], function () {

    // -------------------------------------------------------
    // مسارات عامة (لا تتطلب تسجيل دخول)
    // -------------------------------------------------------
    Route::group([], function () {});

    // -------------------------------------------------------
    // مسارات محمية (تتطلب تسجيل الدخول)
    // -------------------------------------------------------
    Route::group(['middleware' => ['auth:api', 'throttle:60,1']], function () {
        Route::post('/profile', [StoreProfileController::class, 'update'])->name('store.profile.update');
    });
});

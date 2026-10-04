<?php

use App\Http\Controllers\Api\User\UserProfileController;
use Illuminate\Support\Facades\Route;

/**
 * مسارات API للمستخدم — البادئة: /api/user
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
        Route::post('/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    });
});

<?php

use App\Http\Controllers\Api\Store\StoreProductController;
use App\Http\Controllers\Api\Store\StoreProfileController;
use App\Http\Controllers\Api\Store\StoreReviewController;
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
        Route::post('/profile', [StoreProfileController::class, 'update']);
        Route::get('/products', [StoreProductController::class, 'index']);
        Route::get('/products/{product}', [StoreProductController::class, 'show'])->whereNumber('product');
        Route::get('/products/{product}/reviews', [StoreReviewController::class, 'index'])->whereNumber('product');
        Route::post('/products', [StoreProductController::class, 'store']);
        Route::patch('/products/{product}', [StoreProductController::class, 'update'])->whereNumber('product');
        Route::patch('/products/{product}/hide', [StoreProductController::class, 'hide'])->whereNumber('product');
        Route::delete('/products/{product}', [StoreProductController::class, 'destroy'])->whereNumber('product');
    });
});

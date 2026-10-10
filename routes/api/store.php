<?php

use App\Http\Controllers\Api\Store\StoreAdController;
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
    Route::group([], function () {

        // يعرض باقات الإعلانات المتاحة للمتاجر.
        Route::get('/ad-packages', [StoreAdController::class, 'packages']);

    });

    // -------------------------------------------------------
    // مسارات محمية (تتطلب تسجيل الدخول)
    // -------------------------------------------------------
    Route::group(['middleware' => ['auth:api', 'throttle:60,1']], function () {
        // يحدّث بيانات ملف المتجر.
        Route::post('/profile', [StoreProfileController::class, 'update']);
        // يعرض منتجات المتجر.
        Route::get('/products', [StoreProductController::class, 'index']);
        // يعرض تفاصيل منتج محدد في المتجر.
        Route::get('/products/{product}', [StoreProductController::class, 'show'])->whereNumber('product');
        // يعرض تقييمات منتج محدد.
        Route::get('/products/{product}/reviews', [StoreReviewController::class, 'index'])->whereNumber('product');
        // ينشئ منتجًا جديدًا للمتجر.
        Route::post('/products', [StoreProductController::class, 'store']);
        // يحدّث بيانات منتج محدد.
        Route::patch('/products/{product}', [StoreProductController::class, 'update'])->whereNumber('product');
        // يغيّر حالة ظهور المنتج للعملاء.
        Route::patch('/products/{product}/hide', [StoreProductController::class, 'toggleVisibility'])->whereNumber('product');
        // يحذف منتجًا من المتجر.
        Route::delete('/products/{product}', [StoreProductController::class, 'destroy'])->whereNumber('product');
        // يعرض إعلانات المتجر.
        Route::get('/ads', [StoreAdController::class, 'index']);
        // يعرض رصيد المحفظة المتاح للتحقق من تكلفة الإعلانات.
        Route::get('/ads/wallet-balance', [StoreAdController::class, 'walletBalance']);
        // يعرض تفاصيل إعلان محدد.
        Route::get('/ads/{ad}', [StoreAdController::class, 'show'])->whereNumber('ad');
        // ينشئ إعلانًا كمسودة أو يدفعه للمراجعة بالمحفظة أو ينشئ له دفعة إلكترونية معلقة.
        Route::post('/ads', [StoreAdController::class, 'store']);
        // يدفع تكلفة الإعلان المحفوظ بالمحفظة لإرساله للمراجعة أو ينشئ دفعة إلكترونية معلقة له.
        Route::post('/ads/{ad}/submit', [StoreAdController::class, 'submit'])->whereNumber('ad');
        // يحدّث بيانات إعلان محدد.
        Route::patch('/ads/{ad}', [StoreAdController::class, 'update'])->whereNumber('ad');
        // يغيّر حالة تفعيل الإعلان.
        Route::patch('/ads/{ad}/toggle', [StoreAdController::class, 'toggle'])->whereNumber('ad');
        // يحذف إعلانًا من المتجر.
        Route::delete('/ads/{ad}', [StoreAdController::class, 'destroy'])->whereNumber('ad');
    });
});

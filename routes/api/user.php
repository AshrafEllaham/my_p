<?php

use App\Http\Controllers\Api\User\UserFavoriteController;
use App\Http\Controllers\Api\User\UserHomeCategoryController;
use App\Http\Controllers\Api\User\UserProductController;
use App\Http\Controllers\Api\User\UserProfileController;
use Illuminate\Support\Facades\Route;

/**
 * مسارات API للمستخدم — البادئة: /api/user
 */
Route::group([], function () {

    // -------------------------------------------------------
    // مسارات عامة (لا تتطلب تسجيل دخول)
    // -------------------------------------------------------
    Route::group([], function () {
        // يعرض الأقسام الرئيسية النشطة للمستخدم مع البحث والترقيم.
        Route::get('/main-categories', [UserHomeCategoryController::class, 'index'])->middleware('throttle:60,1');

        // يعرض الأقسام الفرعية النشطة التابعة لقسم رئيسي للمستخدم.
        Route::get('/sub-categories', [UserHomeCategoryController::class, 'subCategories'])->middleware('throttle:60,1');

    });

    // مسارات محمية (تتطلب تسجيل الدخول)
    Route::group(['middleware' => ['auth:api', 'throttle:60,1']], function () {
        // يحدّث بيانات الملف الشخصي للمستخدم.
        Route::post('/profile', [UserProfileController::class, 'update']);

        // يعرض المنتجات المنشورة مع فلاتر الأقسام والمحافظة والبحث والترتيب.
        Route::get('/products', [UserProductController::class, 'index'])->middleware('throttle:60,1');

        // يبدل حالة منتج أو متجر في مفضلة المستخدم الحالي.
        Route::post('/favorites/toggle', [UserFavoriteController::class, 'toggle']);

    });
});

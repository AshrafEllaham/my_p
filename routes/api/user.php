<?php

use App\Http\Controllers\Api\User\UserCompetitionController;
use App\Http\Controllers\Api\User\UserFavoriteController;
use App\Http\Controllers\Api\User\UserHomeCategoryController;
use App\Http\Controllers\Api\User\UserProductController;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Http\Controllers\Api\User\UserReportController;
use App\Http\Controllers\Api\User\UserStoreController;
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

        // يعرض منتجات مقترحة حسب مشتريات المستخدم ومفضلاته ومحافظته.
        Route::get('/products/recommended', [UserProductController::class, 'recommended']);

        // يعرض المتاجر مرتبة حسب القرب عند إرسال الإحداثيات أو حسب الأعلى تقييمًا عند غيابها.
        Route::get('/stores/nearby', [UserStoreController::class, 'nearby']);

        // يعرض مسابقات المتاجر المتاحة أو المنتهية مع تقسيم النتائج إلى صفحات.
        Route::get('/competitions', [UserCompetitionController::class, 'index']);

        // يعرض منتجات متجر محدد مع البحث والترتيب وتقسيم الصفحات.
        Route::get('/stores/{store}/products', [UserStoreController::class, 'products'])->whereNumber('store');

        // يعرض الإعلانات النشطة ضمن فترة العرض لمتجر محدد مع تقسيم النتائج إلى صفحات.
        Route::get('/stores/{store}/ads', [UserStoreController::class, 'ads'])->whereNumber('store');

        // يعرض بيانات متجر نشط وسياساته وأبرز منتجاته للمستخدم.
        Route::get('/stores/{store}', [UserStoreController::class, 'show'])->whereNumber('store');

        // يعرض تفاصيل منتج منشور مع المتجر والسياسات والتقييمات ومنتجات مشابهة.
        Route::get('/products/{product}', [UserProductController::class, 'show'])->whereNumber('product');

        // يعرض منتجات مشابهة مع فلاتر الكتالوج وتقسيم الصفحات.
        Route::get('/products/{product}/similar-products', [UserProductController::class, 'similar'])->whereNumber('product');

        // يعرض تقييمات المنتج الظاهرة مع ملخصها وتقسيم الصفحات.
        Route::get('/products/{product}/reviews', [UserProductController::class, 'reviews'])->whereNumber('product');

        // يعرض أسباب الإبلاغ المترجمة المفلترة حسب نوع العنصر.
        Route::get('/report-reasons', [UserReportController::class, 'reasons']);

        // يسجل بلاغ المستخدم عن منتج منشور بسبب محدد.
        Route::post('/products/{product}/reports', [UserReportController::class, 'storeProduct'])->whereNumber('product');

        // يسجل بلاغ المستخدم عن متجر نشط بسبب محدد.
        Route::post('/stores/{store}/reports', [UserReportController::class, 'storeStoreReport'])->whereNumber('store');

        // يبدل حالة منتج أو متجر في مفضلة المستخدم الحالي.
        Route::post('/favorites/toggle', [UserFavoriteController::class, 'toggle']);

    });
});

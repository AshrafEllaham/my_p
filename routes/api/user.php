<?php

use App\Http\Controllers\Api\User\UserAdController;
use App\Http\Controllers\Api\User\UserCompetitionController;
use App\Http\Controllers\Api\User\UserFavoriteController;
use App\Http\Controllers\Api\User\UserHomeCategoryController;
use App\Http\Controllers\Api\User\UserOrderController;
use App\Http\Controllers\Api\User\UserProductController;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Http\Controllers\Api\User\UserReportController;
use App\Http\Controllers\Api\User\UserStoreController;
use App\Http\Controllers\Api\User\WalletController;
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
        // يعرض الإعلانات النشطة حسب موضع ظهورها مع تقسيم النتائج إلى صفحات.
        Route::get('/ads', [UserAdController::class, 'index']);

        // يسجل مشاهدة إعلان نشط مرة واحدة لكل معرّف حدث.
        Route::post('/ads/{ad}/impressions', [UserAdController::class, 'impression'])->whereNumber('ad');

        // يسجل نقرة المستخدم على إعلان نشط مرة واحدة لكل معرّف حدث.
        Route::post('/ads/{ad}/clicks', [UserAdController::class, 'click'])->whereNumber('ad');

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

        // يعرض تفاصيل مسابقة المتجر وإجابة المستخدم الحالي وبيانات الفائز عند إعلان النتيجة.
        Route::get('/competitions/{competition}', [UserCompetitionController::class, 'show'])->whereNumber('competition');

        // يحفظ إجابة المستخدم للمسابقة أو يحدّثها ما دامت المشاركة مفتوحة.
        Route::put('/competitions/{competition}/entry', [UserCompetitionController::class, 'saveAnswer'])->whereNumber('competition');

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

        // يعرض مفضلات المستخدم مع فلترة نوع العنصر وتقسيم الصفحات.
        Route::get('/favorites', [UserFavoriteController::class, 'index']);

        // يعرض سجل عمليات محفظة الحساب الحالي بصفحات مرتبة من الأحدث.
        Route::get('/wallet', [WalletController::class, 'index']);
        // ينشئ محاولة دفع إلكتروني لشحن محفظة الحساب الحالي.
        Route::post('/wallet', [WalletController::class, 'store']);
        // يرسل طلب سحب من محفظة الحساب الحالي للمراجعة.
        Route::post('/wallet/withdrawal-requests', [WalletController::class, 'requestWithdrawal']);
        // يحوّل مبلغًا من محفظة الحساب الحالي إلى مستخدم أو متجر آخر.
        Route::post('/wallet/transfers', [WalletController::class, 'transfer']);

        // يعرض طلبات المستخدم الحالي مقسمة إلى صفحات مع إمكانية التصفية بالحالة.
        Route::get('/orders', [UserOrderController::class, 'index']);
        // ينشئ طلب شراء منتج واحد ويخصم قيمته من محفظة المستخدم.
        Route::post('/orders', [UserOrderController::class, 'store']);
        // يعرض تفاصيل طلب يملكه المستخدم الحالي.
        Route::get('/orders/{order}', [UserOrderController::class, 'show'])->whereNumber('order');
        // يعرض إيصال الدفع الخاص بطلب يملكه المستخدم الحالي.
        Route::get('/orders/{order}/receipt', [UserOrderController::class, 'receipt'])->whereNumber('order');
        // يلغي طلب المستخدم قبل بدء تجهيز المتجر ويعيد المبلغ المدفوع إلى المحفظة.
        Route::patch('/orders/{order}/cancel', [UserOrderController::class, 'cancel'])->whereNumber('order');
        // يسجل تقييم المستخدم للمنتج بعد اكتمال الطلب.
        Route::post('/orders/{order}/review', [UserOrderController::class, 'review'])->whereNumber('order');
        // يرسل طلب استرجاع لطلب مكتمل وفق سياسة المتجر.
        Route::post('/orders/{order}/return-requests', [UserOrderController::class, 'requestReturn'])->whereNumber('order');
        // يرسل طلب استبدال لطلب مكتمل وفق سياسة المتجر.
        Route::post('/orders/{order}/exchange-requests', [UserOrderController::class, 'requestExchange'])->whereNumber('order');

    });
});

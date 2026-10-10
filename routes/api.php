<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AccountTypeController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CatalogLookupController;
use App\Http\Controllers\Api\ChangePasswordController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationPreferenceController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

// User (student) routes
Route::prefix('user')->group(base_path('routes/api/user.php'));

// Store routes
Route::prefix('store')->group(base_path('routes/api/store.php'));

Route::group([], function () {

    // -------------------------------------------------------
    // Public Routes (no auth required)
    // -------------------------------------------------------
    Route::group([], function () {
        // يعرض البانرات المتاحة للتطبيق.
        Route::get('/banners', [BannerController::class, 'index'])->middleware('throttle:60,1');
        // يعرض الأسئلة الشائعة وإجاباتها.
        Route::get('/faqs', [FaqController::class, 'index'])->middleware('throttle:60,1');
        // يعرض إعدادات التطبيق العامة.
        Route::get('/settings', [SettingsController::class, 'show'])->middleware('throttle:60,1');
        // يعرض الأقسام الرئيسية.
        Route::get('/main-categories', [CatalogLookupController::class, 'mainCategories'])->middleware('throttle:60,1');
        // يعرض الأقسام الفرعية التابعة لقسم رئيسي.
        Route::get('/sub-categories', [CatalogLookupController::class, 'subCategories'])->middleware('throttle:60,1');
        // يعرض قائمة الدول.
        Route::get('/countries', [CatalogLookupController::class, 'countries'])->middleware('throttle:60,1');
        // يعرض المحافظات التابعة لدولة.
        Route::get('/governorates', [CatalogLookupController::class, 'governorates'])->middleware('throttle:60,1');
        // يعرض المدن التابعة لمحافظة.
        Route::get('/cities', [CatalogLookupController::class, 'cities'])->middleware('throttle:60,1');
        // يرسل رسالة جديدة إلى فريق التواصل.
        Route::post('/contact-us', [ContactUsController::class, 'store'])->middleware('throttle:5,1');
        // يرسل رمز التحقق إلى المستخدم.
        Route::post('/send-otp', [AuthApiController::class, 'sendOtp'])->middleware('throttle:3,1');
        // يتحقق من رمز التحقق ويؤكد رقم الهاتف.
        Route::post('/confirm-otp', [AuthApiController::class, 'confirmOtp'])->middleware('throttle:10,1');
        // ينشئ حساب مستخدم جديدًا.
        Route::post('/register', [AuthApiController::class, 'store'])->middleware('throttle:5,1');
        // يسجل دخول المستخدم ببياناته.
        Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:5,1');
        // يسجل دخول المستخدم باستخدام مزود هوية اجتماعي.
        Route::post('/social-login', [AuthApiController::class, 'socialLogin'])->middleware('throttle:10,1');
        // يغيّر نوع الحساب للحساب المحدد.
        Route::patch('/accounts/{account}/type', [AccountTypeController::class, 'update'])->whereNumber('account')->middleware('throttle:10,1');
    });

    // -------------------------------------------------------
    // Protected Routes (Require Authentication)
    // -------------------------------------------------------
    Route::group(['middleware' => ['auth:api', 'throttle:60,1']], function () {
        // ينهي جلسة المستخدم الحالية.
        Route::post('/logout', [AccountController::class, 'logout']);
        // يحذف حساب المستخدم الحالي.
        Route::delete('/account', [AccountController::class, 'destroy']);
        // يغيّر كلمة مرور المستخدم الحالي.
        Route::put('/change-password', [ChangePasswordController::class, 'update']);

        // ############################ notifications ############################
        // يعرض إشعارات المستخدم الحالي.
        Route::get('/notifications', [NotificationController::class, 'index']);
        // يعلّم جميع إشعارات المستخدم كمقروءة.
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        // يعلّم إشعارًا محددًا كمقروء.
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->whereUuid('notification');
        // يحذف جميع إشعارات المستخدم الحالي.
        Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
        // يعرض تفضيلات إشعارات المستخدم الحالي.
        Route::get('/notifications/preferences', [NotificationPreferenceController::class, 'show']);
        // يستبدل تفضيلات إشعارات المستخدم الحالية.
        Route::put('/notifications/preferences', [NotificationPreferenceController::class, 'update']);
        // يحدّث جزءًا من تفضيلات إشعارات المستخدم الحالية.
        Route::patch('/notifications/preferences', [NotificationPreferenceController::class, 'update']);

        // ############################ chat ############################
    });
});

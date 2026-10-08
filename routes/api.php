<?php

use App\Http\Controllers\Api\AccountTypeController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CatalogLookupController;
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
        Route::get('/banners', [BannerController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/faqs', [FaqController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/settings', [SettingsController::class, 'show'])->middleware('throttle:60,1');
        Route::get('/main-categories', [CatalogLookupController::class, 'mainCategories'])->middleware('throttle:60,1');
        Route::get('/sub-categories', [CatalogLookupController::class, 'subCategories'])->middleware('throttle:60,1');
        Route::get('/countries', [CatalogLookupController::class, 'countries'])->middleware('throttle:60,1');
        Route::get('/governorates', [CatalogLookupController::class, 'governorates'])->middleware('throttle:60,1');
        Route::get('/cities', [CatalogLookupController::class, 'cities'])->middleware('throttle:60,1');
        Route::post('/contact-us', [ContactUsController::class, 'store'])->middleware('throttle:5,1');
        Route::post('/send-otp', [AuthApiController::class, 'sendOtp'])->middleware('throttle:3,1');
        Route::post('/confirm-otp', [AuthApiController::class, 'confirmOtp'])->middleware('throttle:10,1');
        Route::post('/register', [AuthApiController::class, 'store'])->middleware('throttle:5,1');
        Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/social-login', [AuthApiController::class, 'socialLogin'])->middleware('throttle:10,1');
        Route::patch('/accounts/{account}/type', [AccountTypeController::class, 'update'])->whereNumber('account')->middleware('throttle:10,1');
    });

    // -------------------------------------------------------
    // Protected Routes (Require Authentication)
    // -------------------------------------------------------
    Route::group(['middleware' => ['auth:api', 'throttle:60,1']], function () {

        // ############################ notifications ############################
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->whereUuid('notification');
        Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
        Route::get('/notifications/preferences', [NotificationPreferenceController::class, 'show']);
        Route::put('/notifications/preferences', [NotificationPreferenceController::class, 'update']);

        // ############################ chat ############################
    });
});

<?php

use App\Http\Controllers\Api\AccountTypeController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationPreferenceController;
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
        Route::post('/send-otp', [AuthApiController::class, 'sendOtp'])->middleware('throttle:3,1')->name('auth.send-otp');
        Route::post('/confirm-otp', [AuthApiController::class, 'confirmOtp'])->middleware('throttle:10,1')->name('auth.confirm-otp');
        Route::post('/register', [AuthApiController::class, 'store'])->middleware('throttle:5,1')->name('user.register');
        Route::patch('/accounts/{account}/type', [AccountTypeController::class, 'update'])->whereNumber('account')->middleware('throttle:10,1')->name('account.type.update');
    });

    // -------------------------------------------------------
    // Protected Routes (Require Authentication)
    // -------------------------------------------------------
    Route::group(['middleware' => ['auth:api', 'throttle:60,1']], function () {

        // ############################ notifications ############################
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->whereUuid('notification')
            ->name('notifications.read');
        Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        Route::get('/notifications/preferences', [NotificationPreferenceController::class, 'show'])->name('notifications.preferences.show');
        Route::put('/notifications/preferences', [NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');

        // ############################ chat ############################
    });
});

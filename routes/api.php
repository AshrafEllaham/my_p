<?php

use App\Http\Controllers\Api\AuthApiController;
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
    });

    // -------------------------------------------------------
    // Protected Routes (Require Authentication)
    // -------------------------------------------------------
    Route::group(['middleware' => ['auth:api']], function () {

        // ############################ notifications ############################

        // ############################ chat ############################
    });
});

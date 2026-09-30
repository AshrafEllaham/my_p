<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\HomeController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
], function () {

    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [LoginController::class, 'show'])->name('admin.login');
        Route::post('/login', [LoginController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('admin.login.post');
    });

    Route::middleware('auth:admin')->group(function (): void {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.index');
        Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');
    });
});

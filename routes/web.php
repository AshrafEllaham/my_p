<?php

use App\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
    ],
    function () {

        Route::get('privacy', [HomeController::class, 'privacy'])->name('web.privacy');
        Route::get('terms-conditions', [HomeController::class, 'terms_conditions'])->name('web.terms_conditions');
        Route::get('about-app', [HomeController::class, 'about_app'])->name('web.about_app');
    }
);

Route::get('pay-online/{id}/{type}', [HomeController::class,'pay_online'])->name('web.pay_online');
Route::get('payment-success/{id}/{type}',[HomeController::class,'paymentSuccess'])->name("payment.success");
Route::get('payment-failed/{id}/{type}',[HomeController::class,'paymentFailed'])->name("payment.failed");

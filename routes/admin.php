<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\Developer\CommandController;
use App\Http\Controllers\Admin\Developer\TerminalController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GovernorateController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\MainCategoryController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubCategoryController;
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

    Route::middleware(['auth:admin', 'throttle:60,1'])->group(function (): void {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.index');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
        Route::get('/settings', [SettingsController::class, 'edit'])->name('admin.settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
        Route::prefix('developer')->name('admin.developer.')->middleware('developer')->group(function (): void {
            Route::get('/commands', [CommandController::class, 'index'])->name('commands.index');
            Route::get('/commands/create', [CommandController::class, 'create'])->name('commands.create');
            Route::get('/commands/{command}/edit', [CommandController::class, 'edit'])->name('commands.edit');
            Route::post('/commands', [CommandController::class, 'store'])->name('commands.store');
            Route::put('/commands/{command}', [CommandController::class, 'update'])->name('commands.update');
            Route::delete('/commands/{command}', [CommandController::class, 'destroy'])->name('commands.destroy');
            Route::get('/terminal', [TerminalController::class, 'index'])->name('terminal.index');
            Route::post('/terminal', [TerminalController::class, 'run'])->name('terminal.run');
        });
        Route::resources([
            'countries' => CountryController::class,
            'governorates' => GovernorateController::class,
            'cities' => CityController::class,
            'main-categories' => MainCategoryController::class,
            'sub-categories' => SubCategoryController::class,
            'banners' => BannerController::class,
            'faqs' => FaqController::class,
        ], [
            'as' => 'admin',
        ]);
        Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');
    });
});

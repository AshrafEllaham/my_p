<?php

namespace App\Providers;

use App\Contracts\Sai\SocialIdentityVerifier;
use App\Enums\AdminTypeEnum;
use App\Models\Sai\Settings;
use App\Repositories\Sai\SettingsRepository;
use App\Services\Sai\OidcSocialIdentityVerifier;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\ServiceProvider;
use Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    use LoadsTranslatedCachedRoutes;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SocialIdentityVerifier::class, OidcSocialIdentityVerifier::class);
        $this->app->singleton('settings', function ($app): Settings {
            return $app->make(SettingsRepository::class)->getSingleton() ?? new Settings;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RouteServiceProvider::loadCachedRoutesUsing(fn () => $this->loadCachedRoutes());

        $migrationsPath = database_path('migrations');
        $directories = glob($migrationsPath.'/*', GLOB_ONLYDIR);
        $paths = array_merge([$migrationsPath], $directories);

        $this->loadMigrationsFrom($paths);

        LogViewer::auth(function ($request) {
            return $request->user('admin')?->admin_type === AdminTypeEnum::Developer;
        });
    }
}

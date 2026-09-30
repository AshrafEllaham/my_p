<?php

namespace Tests\Feature\Admin;

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_home_renders_the_master_layout(): void
    {
        $this->withoutVite();
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);
        $admin = Admin::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.home.index')
            ->assertSee('data-page-loader', false)
            ->assertSee(__('admin.loading.message'))
            ->assertSee(__('admin.welcome'))
            ->assertSee(__('admin.panel_name'));
    }

    public function test_dashboard_uses_the_saved_english_locale(): void
    {
        $this->withoutVite();
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);
        $admin = Admin::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $englishUrl = LaravelLocalization::getLocalizedURL('en', route('admin.index'), [], true);
        $this->assertStringContainsString('/en/dashboard', $englishUrl);

        app()->setLocale('en');
        LaravelLocalization::setLocale('en');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.index'));

        $response
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Welcome to the Saey dashboard')
            ->assertSee('العربية');
    }
}

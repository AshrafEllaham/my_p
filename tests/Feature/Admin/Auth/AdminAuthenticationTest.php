<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_login_page_is_available_to_guests(): void
    {
        $this->withoutVite();
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);

        $response = $this->get(route('admin.login'));

        $response
            ->assertOk()
            ->assertViewIs('admin.auth.login')
            ->assertSee('assets/brand/saey-mark.svg', false)
            ->assertSee(__('admin.auth.login_title'));
    }

    public function test_guest_can_switch_login_page_to_english_and_back_to_arabic(): void
    {
        $this->withoutVite();
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);

        $englishUrl = LaravelLocalization::getLocalizedURL('en', route('admin.login'), [], true);
        $this->assertStringContainsString('/en/login', $englishUrl);

        app()->setLocale('en');
        LaravelLocalization::setLocale('en');
        $englishResponse = $this->get(route('admin.login'));

        $englishResponse
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Admin sign in')
            ->assertSee('العربية');

        $arabicUrl = LaravelLocalization::getLocalizedURL('ar', $englishUrl, [], true);
        $this->assertStringContainsString('/ar/login', $arabicUrl);

        app()->setLocale('ar');
        LaravelLocalization::setLocale('ar');
        $arabicResponse = $this->get(route('admin.login'));

        $arabicResponse
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('تسجيل دخول المشرف')
            ->assertSee('English');
    }

    public function test_unsupported_admin_locale_is_rejected(): void
    {
        $this->get('/fr/login')->assertNotFound();
    }

    public function test_active_admin_can_login(): void
    {
        $admin = $this->createAdmin();

        $response = $this->post(route('admin.login.post'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertNotNull($admin->fresh()?->last_login_at);
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $admin = $this->createAdmin(false);

        $response = $this->from(route('admin.login'))->post(route('admin.login.post'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_blocked_admin_cannot_login(): void
    {
        $admin = $this->createAdmin(isBlocked: true);

        $response = $this->from(route('admin.login'))->post(route('admin.login.post'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    private function createAdmin(bool $isActive = true, bool $isBlocked = false): Admin
    {
        return Admin::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'is_active' => $isActive,
            'is_blocked' => $isBlocked,
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);
    }

    public function test_guest_cannot_open_admin_profile(): void
    {
        $this->get(route('admin.profile.edit'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_open_profile_from_header(): void
    {
        $this->withoutVite();
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.profile.edit'));

        $response
            ->assertOk()
            ->assertViewIs('admin.profile.edit')
            ->assertSee(route('admin.profile.edit'), false)
            ->assertSee(__('admin.profile.details_title'))
            ->assertSee($admin->email);
    }

    public function test_admin_can_update_profile_details(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.profile.update'), [
            'name' => 'Updated Admin',
            'email' => 'updated@example.com',
            'phone' => '01012345678',
        ]);

        $response
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('success', __('admin.profile.updated'));
        $this->assertDatabaseHas('admins', [
            'id' => $admin->id,
            'name' => 'Updated Admin',
            'email' => 'updated@example.com',
            'phone' => '01012345678',
        ]);
        $this->assertTrue(Hash::check('password123', $admin->fresh()->password));
    }

    public function test_admin_can_change_password_using_current_password(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'current_password' => 'password123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('new-password-456', $admin->fresh()->password));
    }

    public function test_profile_update_rejects_wrong_current_password_and_duplicate_email(): void
    {
        $admin = $this->createAdmin();
        $otherAdmin = Admin::query()->create([
            'name' => 'Other Admin',
            'email' => 'other@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.update'), [
                'name' => 'Updated Admin',
                'email' => $otherAdmin->email,
                'current_password' => 'wrong-password',
                'password' => 'new-password-456',
                'password_confirmation' => 'new-password-456',
            ]);

        $response
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHasErrors(['email', 'current_password']);
        $this->assertSame('Admin User', $admin->fresh()->name);
        $this->assertTrue(Hash::check('password123', $admin->fresh()->password));
    }

    private function createAdmin(): Admin
    {
        return Admin::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '01000000000',
            'password' => 'password123',
            'is_active' => true,
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminTypeEnum;
use App\Models\Admin\Admin;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_login_ready_admin_and_developer_accounts(): void
    {
        config()->set('admin.seed.accounts', [
            'admin' => [
                'name' => 'System Admin',
                'email' => 'admin@test.local',
                'phone' => '01000000001',
                'password' => 'password123',
                'type' => AdminTypeEnum::Admin,
            ],
            'developer' => [
                'name' => 'System Developer',
                'email' => 'developer@test.local',
                'phone' => '01000000002',
                'password' => 'password123',
                'type' => AdminTypeEnum::Developer,
            ],
        ]);

        $this->seed(AdminSeeder::class);

        $admin = Admin::query()->where('email', 'admin@test.local')->firstOrFail();
        $developer = Admin::query()->where('email', 'developer@test.local')->firstOrFail();

        $this->assertSame(AdminTypeEnum::Admin, $admin->admin_type);
        $this->assertSame(AdminTypeEnum::Developer, $developer->admin_type);
        $this->assertTrue($admin->is_active);
        $this->assertFalse($admin->is_blocked);
        $this->assertTrue(Hash::check('password123', $admin->password));
        $this->assertTrue(Hash::check('password123', $developer->password));

        $response = $this->post(route('admin.login.post'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}

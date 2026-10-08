<?php

namespace Tests\Feature\Api;

use App\Enums\UserStatusEnum;
use App\Models\Sai\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_invalidates_the_current_api_token(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', __('messages.auth.logout_success'));

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/notifications')->assertUnauthorized();
    }

    public function test_account_deletion_soft_deletes_the_user_and_invalidates_the_session(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);

        $this->withHeader('Accept-Language', 'ar')
            ->withToken($token)
            ->deleteJson('/api/account')
            ->assertOk()
            ->assertJsonPath('message', __('messages.account.deleted'))
            ->assertJsonPath('data', null);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id, 'deleted_at' => null]);
        $this->assertFalse(Schema::hasTable('account_deletion_requests'));
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/notifications')->assertUnauthorized();
    }

    public function test_account_lifecycle_endpoints_require_authentication(): void
    {
        $this->postJson('/api/logout')->assertUnauthorized();
        $this->deleteJson('/api/account')->assertUnauthorized();
    }

    public function test_social_login_is_blocked_after_account_is_soft_deleted(): void
    {
        $user = User::factory()->create([
            'social_id' => 'social-id-deletion-requested',
            'email' => 'deletion-requested@example.com',
            'status' => UserStatusEnum::Active,
        ]);
        $user->delete();

        $this->postJson('/api/social-login', [
            'social_id' => 'social-id-deletion-requested',
            'email' => 'deletion-requested@example.com',
            'name' => 'User',
        ])->assertUnprocessable()
            ->assertJsonPath('message', __('messages.auth.account_unavailable'));
    }

    public function test_account_preview_calls_logout_and_deletion_endpoints(): void
    {
        $userAccount = file_get_contents(base_path('Figma/screens/15-account.html'));
        $storeAccount = file_get_contents(base_path('Figma/screens/20-distributor-account.html'));
        $security = file_get_contents(base_path('Figma/screens/account-security.html'));
        $api = file_get_contents(base_path('Figma/screens/account-api.js'));

        $this->assertIsString($userAccount);
        $this->assertIsString($storeAccount);
        $this->assertIsString($security);
        $this->assertIsString($api);
        $this->assertStringContainsString("request('/logout', 'POST')", $userAccount);
        $this->assertStringContainsString("request('/logout','POST')", $storeAccount);
        $this->assertStringContainsString("request('/account', 'DELETE')", $security);
        $this->assertStringContainsString('account-api.js', $security);
    }

    private function tokenFor(User $user): string
    {
        return (string) auth('api')->login($user);
    }
}

<?php

namespace Tests\Feature\Api;

use App\Contracts\Sai\SocialIdentityVerifier;
use App\Enums\SocialLoginProviderEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'StrongPass123',
            'status' => UserStatusEnum::Active,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => ' LOGIN@example.com ',
            'password' => 'StrongPass123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'login@example.com')
            ->assertJsonPath('data.token_type', 'bearer');
        $this->assertIsString($response->json('data.access_token'));
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_user_can_login_with_phone_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'phone-login@example.com',
            'phone_code' => '+20',
            'phone' => '1012345678',
            'password' => 'StrongPass123',
            'status' => UserStatusEnum::Active,
        ]);

        $this->postJson('/api/login', [
            'phone_code' => '+20',
            'phone' => '1012345678',
            'password' => 'StrongPass123',
        ])->assertOk()->assertJsonPath('data.id', $user->id);
    }

    public function test_wrong_password_or_inactive_account_is_rejected_with_localized_message(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'StrongPass123',
            'status' => UserStatusEnum::Suspended,
        ]);

        $response = $this->withHeader('Accept-Language', 'en')->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'WrongPass123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.identity.0', __('messages.auth.credentials_invalid'));
    }

    public function test_login_validation_requires_one_identity_and_password(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')->postJson('/api/login', []);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.email.0', __('messages.validation.email.required'))
            ->assertJsonPath('errors.password.0', __('messages.validation.password.required'));
    }

    public function test_social_login_verifies_provider_identity_and_returns_api_token(): void
    {
        $this->app->instance(SocialIdentityVerifier::class, Mockery::mock(SocialIdentityVerifier::class, function ($mock): void {
            $mock->shouldReceive('verify')
                ->once()
                ->with(SocialLoginProviderEnum::Google, 'signed-provider-token')
                ->andReturn([
                    'sub' => 'google-sub-123',
                    'email' => 'social@example.com',
                    'email_verified' => true,
                    'name' => 'Social User',
                ]);
        }));

        $response = $this->postJson('/api/social-login', [
            'provider' => 'google',
            'id_token' => 'signed-provider-token',
            'social_id' => 'google-sub-123',
            'email' => 'social@example.com',
            'phone_code' => '+20',
            'phone' => '1012345678',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', 'social@example.com')
            ->assertJsonPath('data.phone', null)
            ->assertJsonPath('data.token_type', 'bearer');
        $this->assertIsString($response->json('data.access_token'));
        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'google',
            'social_id' => 'google-sub-123',
            'provider_email' => 'social@example.com',
        ]);
    }

    public function test_social_login_rejects_client_claims_that_do_not_match_the_signed_token(): void
    {
        $this->app->instance(SocialIdentityVerifier::class, Mockery::mock(SocialIdentityVerifier::class, function ($mock): void {
            $mock->shouldReceive('verify')->once()->andReturn([
                'sub' => 'verified-sub',
                'email' => 'verified@example.com',
                'email_verified' => true,
            ]);
        }));

        $this->postJson('/api/social-login', [
            'provider' => 'apple',
            'id_token' => 'signed-provider-token',
            'social_id' => 'spoofed-sub',
            'email' => 'verified@example.com',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.id_token.0', __('messages.auth.social_token_invalid'));

        $this->assertDatabaseCount('users', 0);
    }
}

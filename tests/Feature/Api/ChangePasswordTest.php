<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_and_store_accounts_can_change_password(): void
    {
        foreach ([AccountTypeEnum::User, AccountTypeEnum::Store] as $accountType) {
            $user = User::factory()->create([
                'account_type' => $accountType,
                'password' => 'CurrentPass123',
            ]);

            $this->withToken($this->tokenFor($user))
                ->putJson('/api/change-password', [
                    'current_password' => 'CurrentPass123',
                    'password' => 'NewStrongPass456',
                    'password_confirmation' => 'NewStrongPass456',
                ])
                ->assertOk()
                ->assertJsonPath('message', __('messages.auth.password_updated'));

            $this->assertTrue(Hash::check('NewStrongPass456', $user->fresh()->password));
        }
    }

    public function test_change_password_requires_authentication(): void
    {
        $this->putJson('/api/change-password', [
            'current_password' => 'CurrentPass123',
            'password' => 'NewStrongPass456',
            'password_confirmation' => 'NewStrongPass456',
        ])->assertUnauthorized();
    }

    public function test_wrong_current_password_is_rejected_with_localized_message(): void
    {
        $user = User::factory()->create(['password' => 'CurrentPass123']);

        $this->withHeader('Accept-Language', 'en')
            ->withToken($this->tokenFor($user))
            ->putJson('/api/change-password', [
                'current_password' => 'IncorrectPass123',
                'password' => 'NewStrongPass456',
                'password_confirmation' => 'NewStrongPass456',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.auth.current_password_invalid'));
    }

    public function test_password_validation_messages_are_localized(): void
    {
        $user = User::factory()->create(['password' => 'CurrentPass123']);

        $this->withHeader('Accept-Language', 'ar')
            ->withToken($this->tokenFor($user))
            ->putJson('/api/change-password', [
                'current_password' => 'CurrentPass123',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.password.min'));

        $this->withHeader('Accept-Language', 'en')
            ->withToken($this->tokenFor($user))
            ->putJson('/api/change-password', [
                'current_password' => 'CurrentPass123',
                'password' => 'NewStrongPass456',
                'password_confirmation' => 'DifferentPass789',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.password.confirmed'));

        $this->withHeader('Accept-Language', 'en')
            ->withToken($this->tokenFor($user))
            ->putJson('/api/change-password', [
                'current_password' => 'CurrentPass123',
                'password' => 'CurrentPass123',
                'password_confirmation' => 'CurrentPass123',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.change_password.password_different'));
    }

    public function test_security_preview_calls_the_password_endpoint(): void
    {
        $screen = file_get_contents(base_path('Figma/screens/account-security.html'));

        $this->assertIsString($screen);
        $this->assertStringContainsString('/api/change-password', $screen);
        $this->assertStringContainsString('current_password: currentPassword', $screen);
        $this->assertStringContainsString('password_confirmation: passwordConfirmation', $screen);
    }

    private function tokenFor(User $user): string
    {
        return (string) auth('api')->login($user);
    }
}

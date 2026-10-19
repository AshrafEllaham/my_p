<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\OtpPurposeEnum;
use App\Models\Sai\OneTimePassword;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_confirm_and_use_password_reset_code(): void
    {
        $user = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'email' => 'reset@example.test',
        ]);

        $this->postJson('/api/password-reset/send-otp', ['identity' => $user->email])
            ->assertOk()
            ->assertJsonPath('message', __('messages.auth.password_reset_otp_sent'));

        $this->assertDatabaseHas('one_time_passwords', [
            'user_id' => $user->id,
            'identity' => $user->email,
            'purpose' => OtpPurposeEnum::PasswordReset->value,
        ]);

        $this->postJson('/api/password-reset/confirm-otp', [
            'identity' => $user->email,
            'code' => '1234',
        ])->assertOk()->assertJsonPath('data.verified', true);

        $this->postJson('/api/password-reset', [
            'identity' => $user->email,
            'code' => '1234',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertOk()->assertJsonPath('data.updated', true);

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
        $this->assertNotNull(OneTimePassword::query()->where('user_id', $user->id)->value('consumed_at'));
    }

    public function test_password_reset_does_not_disclose_unknown_identities_and_localizes_validation(): void
    {
        $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/password-reset/send-otp', ['identity' => 'missing@example.test'])
            ->assertOk()
            ->assertJsonPath('message', __('messages.auth.password_reset_otp_sent'));

        $this->postJson('/api/password-reset/confirm-otp', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.password_reset.identity_required'));
    }
}

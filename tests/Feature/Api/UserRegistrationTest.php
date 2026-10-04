<?php

namespace Tests\Feature\Api;

use App\Enums\OtpPurposeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\OneTimePassword;
use App\Models\Sai\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_send_and_confirm_otp_before_registering(): void
    {
        $sendResponse = $this->postJson('/api/send-otp', $this->phoneData());
        $sendResponse->assertOk();
        $this->assertIsString($sendResponse->json('data.verification_expires_at'));

        $otp = OneTimePassword::query()->where('identity', '+201012345678')->firstOrFail();
        $this->assertSame(OtpPurposeEnum::Registration, $otp->purpose);
        $this->assertStringStartsWith('$2y$', $otp->code_hash);
        $this->assertTrue(Hash::check('1234', $otp->code_hash));

        $this->postJson('/api/confirm-otp', [...$this->phoneData(), 'code' => '1234'])
            ->assertOk()
            ->assertJsonPath('data.verified', true);

        $response = $this->postJson('/api/register', $this->registrationData());

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'new.user@example.com')
            ->assertJsonPath('data.phone_code', '+20')
            ->assertJsonPath('data.phone', '1012345678')
            ->assertJsonPath('data.status', UserStatusEnum::Active->value)
            ->assertJsonPath('data.verification_required', false)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.otp');

        $user = User::query()->where('email', 'new.user@example.com')->firstOrFail();

        $this->assertNull($user->name);
        $this->assertTrue(Hash::check('StrongPass123', $user->password));

        $otp->refresh();
        $this->assertSame(OtpPurposeEnum::Registration, $otp->purpose);
        $this->assertSame('+201012345678', $otp->identity);
        $this->assertTrue($otp->expires_at->isFuture());
        $this->assertNotNull($otp->verified_at);
        $this->assertNotNull($otp->consumed_at);
        $this->assertSame($user->id, $otp->user_id);
    }

    public function test_registration_validation_messages_are_localized_and_duplicates_are_rejected(): void
    {
        app()->setLocale('en');

        $this->sendAndConfirmOtp();
        $firstResponse = $this->withHeader('Accept-Language', 'en')->postJson('/api/register', $this->registrationData());
        $firstResponse->assertOk();

        $response = $this->withHeader('Accept-Language', 'en')->postJson('/api/register', [
            ...$this->registrationData(),
            'email' => 'NEW.USER@example.com',
            'password_confirmation' => 'DifferentPass123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation_failed'))
            ->assertJsonPath('errors.email.0', __('messages.validation.email.unique'))
            ->assertJsonPath('errors.phone.0', __('messages.validation.phone.unique'))
            ->assertJsonPath('errors.password.0', __('messages.validation.password.confirmed'));
    }

    public function test_registration_endpoint_allows_the_figma_preview_origin(): void
    {
        $response = $this->call('OPTIONS', '/api/register', server: [
            'HTTP_ORIGIN' => 'http://localhost:8085',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type',
        ]);

        $response
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function test_registration_validation_messages_are_available_in_arabic(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/register', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation_failed'))
            ->assertJsonPath('errors.phone_code.0', __('messages.validation.phone_code.required'))
            ->assertJsonPath('errors.email.0', __('messages.validation.email.required'))
            ->assertJsonPath('errors.password.0', __('messages.validation.password.required'));
    }

    public function test_registration_is_rejected_until_phone_is_confirmed(): void
    {
        $response = $this->postJson('/api/register', $this->registrationData());

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.phone.0', __('messages.auth.registration_otp_required'));

        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_otp_is_rejected_and_attempt_is_recorded(): void
    {
        $this->postJson('/api/confirm-otp', $this->phoneData())
            ->assertUnprocessable()
            ->assertJsonPath('errors.code.0', __('messages.validation.otp.required'));

        $this->postJson('/api/send-otp', $this->phoneData())->assertOk();

        $this->postJson('/api/confirm-otp', [...$this->phoneData(), 'code' => '0000'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.code.0', __('messages.auth.otp_invalid_or_expired'));

        $this->assertDatabaseHas('one_time_passwords', ['identity' => '+201012345678', 'attempts' => 1]);
    }

    /** @return array<string, string> */
    private function registrationData(): array
    {
        return [
            'phone_code' => '+20',
            'phone' => '1012345678',
            'email' => 'new.user@example.com',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ];
    }

    /** @return array{phone_code: string, phone: string} */
    private function phoneData(): array
    {
        return ['phone_code' => '+20', 'phone' => '1012345678'];
    }

    private function sendAndConfirmOtp(): void
    {
        $this->postJson('/api/send-otp', $this->phoneData())->assertOk();
        $this->postJson('/api/confirm-otp', [...$this->phoneData(), 'code' => '1234'])->assertOk();
    }
}

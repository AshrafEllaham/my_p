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

    public function test_guest_can_register_and_a_hashed_registration_otp_is_prepared(): void
    {
        $response = $this->postJson('/api/register', $this->registrationData());

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'new.user@example.com')
            ->assertJsonPath('data.phone_code', '+20')
            ->assertJsonPath('data.phone', '1012345678')
            ->assertJsonPath('data.status', UserStatusEnum::PendingVerification->value)
            ->assertJsonPath('data.verification_required', true)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.otp');

        $user = User::query()->where('email', 'new.user@example.com')->firstOrFail();

        $this->assertNull($user->name);
        $this->assertTrue(Hash::check('StrongPass123', $user->password));

        $otp = OneTimePassword::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(OtpPurposeEnum::Registration, $otp->purpose);
        $this->assertSame('+201012345678', $otp->identity);
        $this->assertTrue($otp->expires_at->isFuture());
        $this->assertStringStartsWith('$2y$', $otp->code_hash);
    }

    public function test_registration_validation_messages_are_localized_and_duplicates_are_rejected(): void
    {
        app()->setLocale('en');

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
}

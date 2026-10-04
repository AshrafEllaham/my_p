<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\City;
use App\Models\Sai\Country;
use App\Models\Sai\Governorate;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAccountTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_update_account_type_and_optional_location(): void
    {
        $user = UserFactory::new()->create();
        $country = Country::factory()->create();
        $governorate = Governorate::factory()->create(['country_id' => $country->id]);
        $city = City::factory()->create(['governorate_id' => $governorate->id]);

        $response = $this->patchJson("/api/accounts/{$user->id}/type", [
            'account_type' => AccountTypeEnum::Store->value,
            'country_id' => $country->id,
            'governorate_id' => $governorate->id,
            'city_id' => $city->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.account_type', AccountTypeEnum::Store->value)
            ->assertJsonPath('data.country_id', $country->id)
            ->assertJsonPath('data.governorate_id', $governorate->id)
            ->assertJsonPath('data.city_id', $city->id)
            ->assertJsonPath('message', __('messages.account.type_updated'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_type' => AccountTypeEnum::Store->value,
            'country_id' => $country->id,
            'governorate_id' => $governorate->id,
            'city_id' => $city->id,
        ]);
    }

    public function test_location_is_optional_and_omitted_values_are_preserved(): void
    {
        $country = Country::factory()->create();
        $user = UserFactory::new()->create(['country_id' => $country->id]);

        $this->patchJson("/api/accounts/{$user->id}/type", [
            'account_type' => AccountTypeEnum::User->value,
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_type' => AccountTypeEnum::User->value,
            'country_id' => $country->id,
        ]);
    }

    public function test_changing_country_clears_stale_governorate_and_city(): void
    {
        $oldCountry = Country::factory()->create();
        $oldGovernorate = Governorate::factory()->create(['country_id' => $oldCountry->id]);
        $oldCity = City::factory()->create(['governorate_id' => $oldGovernorate->id]);
        $newCountry = Country::factory()->create();
        $user = UserFactory::new()->create([
            'country_id' => $oldCountry->id,
            'governorate_id' => $oldGovernorate->id,
            'city_id' => $oldCity->id,
        ]);

        $this->patchJson("/api/accounts/{$user->id}/type", [
            'account_type' => AccountTypeEnum::User->value,
            'country_id' => $newCountry->id,
        ])->assertOk();

        $user->refresh();

        $this->assertSame($newCountry->id, $user->country_id);
        $this->assertNull($user->governorate_id);
        $this->assertNull($user->city_id);
    }

    public function test_validation_is_localized_and_rejects_invalid_type_and_location_hierarchy(): void
    {
        $user = UserFactory::new()->create();
        $country = Country::factory()->create();
        $otherCountry = Country::factory()->create();
        $governorate = Governorate::factory()->create(['country_id' => $otherCountry->id]);

        $response = $this->withHeader('Accept-Language', 'en')
            ->patchJson("/api/accounts/{$user->id}/type", [
                'account_type' => 'merchant',
                'country_id' => $country->id,
                'governorate_id' => $governorate->id,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation_failed'))
            ->assertJsonPath('errors.account_type.0', __('messages.validation.account_type.enum'))
            ->assertJsonPath('errors.governorate_id.0', __('messages.validation.governorate_id.exists'));
    }

    public function test_account_type_is_required_with_an_arabic_validation_message(): void
    {
        $user = UserFactory::new()->create();

        $this->withHeader('Accept-Language', 'ar')
            ->patchJson("/api/accounts/{$user->id}/type", [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.account_type.0', __('messages.validation.account_type.required'));
    }

    public function test_unknown_account_returns_not_found(): void
    {
        $this->patchJson('/api/accounts/999999/type', [
            'account_type' => AccountTypeEnum::User->value,
        ])->assertNotFound();
    }

    public function test_figma_onboarding_flow_calls_the_public_endpoint(): void
    {
        $screen = file_get_contents(base_path('Figma/screens/04-account-type.html'));

        $this->assertIsString($screen);
        $this->assertStringContainsString('/api/accounts/${signupDraft.userId}/type', $screen);
        $this->assertStringContainsString("method: 'PATCH'", $screen);
        $this->assertStringContainsString('account_type: selectedAccountType', $screen);
    }
}

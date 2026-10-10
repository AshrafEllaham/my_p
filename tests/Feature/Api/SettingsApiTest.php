<?php

namespace Tests\Feature\Api;

use App\Models\Sai\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_response_includes_configured_ad_placement_prices(): void
    {
        Settings::query()->create([
            'ad_home_price' => '125.50',
            'ad_category_price' => '75.00',
        ]);

        $this->getJson('/api/settings')
            ->assertOk()
            ->assertJsonPath('data.ad_pricing.home', '125.50')
            ->assertJsonPath('data.ad_pricing.category', '75.00');
    }
}

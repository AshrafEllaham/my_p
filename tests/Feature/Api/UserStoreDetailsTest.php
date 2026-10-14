<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\City;
use App\Models\Sai\Governorate;
use App\Models\Sai\Product;
use App\Models\Sai\Store;
use App\Models\Sai\StorePolicyVersion;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserStoreDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_active_store_details_with_policy_products_and_distance(): void
    {
        $category = Category::factory()->create(['ar' => ['name' => 'ملابس']]);
        $governorate = Governorate::factory()->create(['ar' => ['name' => 'القاهرة']]);
        $city = City::factory()->create([
            'governorate_id' => $governorate->id,
            'ar' => ['name' => 'مدينة نصر'],
        ]);
        $owner = UserFactory::new()->create([
            'name' => 'متجر الاختبار',
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
            'governorate_id' => $governorate->id,
            'city_id' => $city->id,
            'address_line' => 'شارع عباس العقاد',
            'phone_code' => '+20',
            'phone' => '1000000000',
        ]);
        $customer = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
        $store = Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'description' => 'وصف المتجر',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);
        StorePolicyVersion::query()->create([
            'store_id' => $owner->id,
            'version' => 1,
            'returns_enabled' => true,
            'return_window_days' => 7,
            'return_terms' => 'المنتج بحالته الأصلية',
            'effective_at' => now(),
        ]);
        $product = Product::query()->create([
            'store_id' => $owner->id,
            'category_id' => $category->id,
            'name' => 'قميص قطني',
            'description' => 'قميص قطني يومي',
            'slug' => Str::random(12),
            'status' => ProductStatusEnum::Published,
            'price' => 620,
            'stock_quantity' => 4,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($customer, 'api')->withHeader('Accept-Language', 'ar')->getJson(
            '/api/user/stores/'.$store->id.'?lat=30.0444&long=31.2357',
        );

        $response->assertOk()
            ->assertJsonPath('message', __('messages.user_stores.retrieved'))
            ->assertJsonPath('data.id', $store->id)
            ->assertJsonPath('data.owner_id', $owner->id)
            ->assertJsonPath('data.name', 'متجر الاختبار')
            ->assertJsonPath('data.category_name', 'ملابس')
            ->assertJsonPath('data.governorate_name', 'القاهرة')
            ->assertJsonPath('data.city_name', 'مدينة نصر')
            ->assertJsonPath('data.average_rating', null)
            ->assertJsonPath('data.distance_km', 0)
            ->assertJsonPath('data.products_count', 1)
            ->assertJsonPath('data.policy.return_window_days', 7)
            ->assertJsonPath('data.policy.return_terms', 'المنتج بحالته الأصلية')
            ->assertJsonPath('data.products.0.id', $product->id)
            ->assertJsonPath('data.products.0.name', 'قميص قطني');
    }

    public function test_store_details_require_auth_validate_location_pair_and_hide_unavailable_stores(): void
    {
        $category = Category::factory()->create();
        $owner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $store = Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'description' => 'Store',
        ]);

        $this->getJson('/api/user/stores/'.$store->id)->assertUnauthorized();
        $customer = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
        $this->actingAs($customer, 'api');
        $this->getJson('/api/user/stores/'.$store->id.'?lat=30.1')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.nearby_stores.long.required_with'));
        $this->withHeader('Accept-Language', 'ar')->getJson('/api/user/stores/0')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_store_details.store.min'));
        $this->withHeader('Accept-Language', 'en')->getJson('/api/user/stores/'.$store->id.'?lat=91&long=31')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Latitude must be between -90 and 90.');
        $this->getJson('/api/user/stores/999999')->assertNotFound();

        $owner->update(['status' => UserStatusEnum::Suspended]);
        $this->getJson('/api/user/stores/'.$store->id)->assertNotFound();
    }

    public function test_store_details_preview_requests_api_and_renders_live_data(): void
    {
        $preview = file_get_contents(base_path('Figma/screens/19-store-details.html'));
        $script = file_get_contents(base_path('Figma/screens/user-store-details.js'));

        $this->assertIsString($preview);
        $this->assertIsString($script);
        $this->assertStringContainsString('user-store-details.js', $preview);
        $this->assertStringContainsString('/api/user/stores/', $script);
        $this->assertStringContainsString('renderPolicy(details.policy)', $script);
        $this->assertStringContainsString('loadStoreProducts()', $script);
        $this->assertStringContainsString('/products?${query}', $script);
        $this->assertStringContainsString('id="storeDetailsState"', $preview);
    }
}

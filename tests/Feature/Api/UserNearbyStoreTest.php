<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\FavoriteTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Favorite;
use App\Models\Sai\Governorate;
use App\Models\Sai\Order;
use App\Models\Sai\Product;
use App\Models\Sai\Review;
use App\Models\Sai\Store;
use App\Models\Sai\StorePolicyVersion;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserNearbyStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinates_order_stores_by_distance_and_exclude_stores_without_coordinates(): void
    {
        $category = Category::factory()->create();
        $customer = $this->createUser();
        $near = $this->createStore($category, 'Near store', 30.0450000, 31.2360000);
        $far = $this->createStore($category, 'Far store', 30.1000000, 31.3000000);
        $unlocated = $this->createStore($category, 'Unlocated store');
        $this->recordReview($far, $customer, $category, 5);
        $this->recordReview($near, $customer, $category, 3);
        Favorite::query()->create([
            'user_id' => $customer->id,
            'type' => FavoriteTypeEnum::Store,
            'favoriteable_id' => $near->id,
        ]);

        $response = $this->actingAs($customer, 'api')->getJson(
            '/api/user/stores/nearby?lat=30.0444196&long=31.2357116&limit_per_page=10',
        );

        $response->assertOk()
            ->assertJsonPath('message', __('messages.user_stores.nearby_listed'))
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.id', $near->id)
            ->assertJsonPath('data.0.is_favorite', true)
            ->assertJsonPath('data.1.id', $far->id);

        $this->assertLessThan(
            (float) $response->json('data.1.distance_km'),
            (float) $response->json('data.0.distance_km'),
        );
        $this->assertNotContains($unlocated->id, $response->json('data.*.id'));
    }

    public function test_missing_coordinates_order_stores_by_highest_visible_rating_with_pagination(): void
    {
        $category = Category::factory()->create();
        $customer = $this->createUser();
        $highestRated = $this->createStore($category, 'Highest rated');
        $lowerRated = $this->createStore($category, 'Lower rated');
        $unrated = $this->createStore($category, 'Unrated');
        $this->recordReview($lowerRated, $customer, $category, 3);
        $this->recordReview($highestRated, $customer, $category, 5);

        $response = $this->actingAs($customer, 'api')
            ->getJson('/api/user/stores/nearby?limit_per_page=2&page=1');

        $response->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('data.0.id', $highestRated->id)
            ->assertJsonPath('data.0.average_rating', 5)
            ->assertJsonPath('data.0.distance_km', null)
            ->assertJsonPath('data.1.id', $lowerRated->id);

        $this->getJson('/api/user/stores/nearby?limit_per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.0.id', $unrated->id);
    }

    public function test_nearby_stores_require_authentication_and_validate_coordinate_pair_in_both_languages(): void
    {
        $this->getJson('/api/user/stores/nearby')->assertUnauthorized();
        $customer = $this->createUser();
        $this->actingAs($customer, 'api');

        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/user/stores/nearby?lat=30.0444')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.nearby_stores.long.required_with'));

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/stores/nearby?lat=91&long=31.2')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Latitude must be between -90 and 90.');

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/stores/nearby?sort=nearest')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Latitude is required when sorting by nearest.');
    }

    public function test_store_list_filters_main_category_and_governorate_and_supports_each_sort_order(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $governorate = Governorate::factory()->create();
        $otherGovernorate = Governorate::factory()->create();
        $customer = $this->createUser();
        $matchingOld = $this->createStore($category, 'Matching old store', 30.045, 31.236, $governorate->id);
        $matchingNew = $this->createStore($category, 'Matching new store', 30.050, 31.240, $governorate->id);
        $outsideGovernorate = $this->createStore($category, 'Outside governorate', 30.0445, 31.2358, $otherGovernorate->id);
        $outsideCategory = $this->createStore($otherCategory, 'Outside category', 30.0446, 31.2359, $governorate->id);
        $this->recordReview($matchingOld, $customer, $category, 5);
        $this->recordReview($matchingNew, $customer, $category, 3);
        $this->actingAs($customer, 'api');

        $base = '/api/user/stores/nearby?main_category_id='.$category->id.'&governorate_id='.$governorate->id.'&limit_per_page=10';
        $this->getJson($base.'&sort=high_rate')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.id', $matchingOld->id)
            ->assertJsonPath('data.1.id', $matchingNew->id);

        $this->getJson($base.'&sort=nearest&lat=30.0444&long=31.2357')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.id', $matchingOld->id);

        $this->getJson($base.'&sort=newest')
            ->assertOk()
            ->assertJsonPath('data.0.id', $matchingNew->id);

        $this->getJson($base.'&sort=high_rate&search=Matching new')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $matchingNew->id);

        $this->getJson('/api/user/stores/nearby?sort=unknown')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_store_filters.sort.enum'));
        $this->getJson('/api/user/stores/nearby?search='.str_repeat('a', 121))
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_store_filters.search.max'));
    }

    public function test_home_preview_loads_nearby_stores_with_saved_pickup_coordinates(): void
    {
        $home = file_get_contents(base_path('Figma/screens/08-user-home.html'));
        $script = file_get_contents(base_path('Figma/screens/user-home-nearby-stores.js'));

        $this->assertStringContainsString('id="nearbyStores"', $home);
        $this->assertStringContainsString('user-home-nearby-stores.js', $home);
        $this->assertStringContainsString('/api/user/stores/nearby', $script);
        $this->assertStringContainsString("query.set('lat'", $script);
        $this->assertStringContainsString("query.set('long'", $script);
        $this->assertStringContainsString('saey_pickup_location', $script);

        $storesPage = file_get_contents(base_path('Figma/screens/11-featured-stores.html'));
        $storesScript = file_get_contents(base_path('Figma/screens/user-featured-stores.js'));
        $this->assertStringContainsString('id="featuredStoresList"', $storesPage);
        $this->assertStringContainsString('user-featured-stores.js', $storesPage);
        $this->assertStringContainsString("query.set('search'", $storesScript);
        $this->assertStringContainsString("query.set('main_category_id'", $storesScript);
        $this->assertStringContainsString("query.set('governorate_id'", $storesScript);
        $this->assertStringContainsString("query.set('sort'", $storesScript);
    }

    private function createUser(): object
    {
        return UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
    }

    private function createStore(
        Category $category,
        string $name,
        ?float $latitude = null,
        ?float $longitude = null,
        ?int $governorateId = null,
    ): Store {
        $owner = UserFactory::new()->create([
            'name' => $name,
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
            'governorate_id' => $governorateId,
        ]);

        return Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'description' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    private function recordReview(Store $store, object $customer, Category $category, int $rating): void
    {
        $policy = StorePolicyVersion::query()->create([
            'store_id' => $store->owner_id,
            'version' => 1,
            'effective_at' => now(),
        ]);
        $product = Product::query()->create([
            'store_id' => $store->owner_id,
            'category_id' => $category->id,
            'name' => 'Product '.Str::random(5),
            'description' => 'Review product',
            'slug' => Str::random(12),
            'status' => ProductStatusEnum::Published,
            'price' => 100,
            'stock_quantity' => 1,
            'published_at' => now(),
        ]);
        $order = Order::query()->create([
            'public_id' => (string) Str::uuid(),
            'number' => 'ORDER-'.Str::upper(Str::random(8)),
            'user_id' => $customer->id,
            'store_id' => $store->owner_id,
            'store_policy_version_id' => $policy->id,
            'status' => OrderStatusEnum::Completed,
            'payment_status' => PaymentStatusEnum::Paid,
            'subtotal' => 100,
            'total_amount' => 100,
            'policy_snapshot' => [],
        ]);
        Review::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'store_id' => $store->owner_id,
            'product_id' => $product->id,
            'rating' => $rating,
            'is_visible' => true,
        ]);
    }
}

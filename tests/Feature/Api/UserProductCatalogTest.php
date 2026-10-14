<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\FavoriteTypeEnum;
use App\Enums\MediaTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Favorite;
use App\Models\Sai\Governorate;
use App\Models\Sai\Order;
use App\Models\Sai\OrderItem;
use App\Models\Sai\Product;
use App\Models\Sai\ProductFeature;
use App\Models\Sai\ProductMedia;
use App\Models\Sai\Review;
use App\Models\Sai\Store;
use App\Models\Sai\StorePolicyVersion;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_category_products_and_get_independently_sorted_paginated_lists(): void
    {
        [$store, $customer, $policy] = $this->createStoreAndCustomer();
        $mainCategory = Category::factory()->create();
        $otherMainCategory = Category::factory()->create();
        $shirts = Category::factory()->subcategory($mainCategory)->create();
        $shoes = Category::factory()->subcategory($mainCategory)->create();
        $otherCategory = Category::factory()->subcategory($otherMainCategory)->create();
        $mostPurchased = $this->createProduct($store->id, $shirts->id, 'Cotton shirt');
        $highestRated = $this->createProduct($store->id, $shoes->id, 'Running shoes');
        $outsideMainCategory = $this->createProduct($store->id, $otherCategory->id, 'Leather bag');

        $this->actingAs($customer, 'api');

        $this->recordSale($store->id, $customer->id, $policy->id, $mostPurchased, 5);
        $this->recordSale($store->id, $customer->id, $policy->id, $mostPurchased, 100, OrderStatusEnum::Cancelled);
        $this->recordSale($store->id, $customer->id, $policy->id, $highestRated, 2);
        $this->recordSale($store->id, $customer->id, $policy->id, $outsideMainCategory, 20);
        $this->recordReview($store->id, $customer->id, $policy->id, $mostPurchased, 4);
        $this->recordReview($store->id, $customer->id, $policy->id, $highestRated, 5);

        $this->getJson('/api/user/products?main_category_id='.$mainCategory->id.'&sort=most_purchased&pagination=on&limit_per_page=1&page=1')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.id', $mostPurchased->id)
            ->assertJsonPath('data.0.units_sold', 5);

        $this->getJson('/api/user/products?main_category_id='.$mainCategory->id.'&sort=most_purchased&pagination=on&limit_per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('data.0.id', $highestRated->id);

        $this->getJson('/api/user/products?main_category_id='.$mainCategory->id.'&sort=highest_rated&pagination=on&limit_per_page=10')
            ->assertOk()
            ->assertJsonPath('data.0.id', $highestRated->id)
            ->assertJsonPath('data.0.average_rating', 5);

        $this->getJson('/api/user/products?main_category_id='.$mainCategory->id.'&sub_category_id='.$shirts->id.'&search=cotton')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mostPurchased->id);
    }

    public function test_user_product_filters_have_localized_validation_messages(): void
    {
        [, $customer] = $this->createStoreAndCustomer();
        $this->actingAs($customer, 'api');

        $mainCategory = Category::factory()->create();
        $otherMainCategory = Category::factory()->create();
        $subcategory = Category::factory()->subcategory($otherMainCategory)->create();

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/products?main_category_id='.$mainCategory->id.'&sub_category_id='.$subcategory->id)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The selected active subcategory was not found or does not belong to the selected main category.');

        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/user/products?sort=top_rated')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'ترتيب المنتجات يجب أن يكون most_purchased أو highest_rated أو newest.');

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/products?governorate_id=999999')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_product_filters.governorate_id.exists'));

        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/user/products?governorate_id=invalid')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_product_filters.governorate_id.integer'));
    }

    public function test_user_can_filter_store_governorate_and_sort_newest_with_pagination(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $governorate = Governorate::factory()->create();
        $otherGovernorate = Governorate::factory()->create();
        $store->update(['governorate_id' => $governorate->id]);
        $otherStore = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
            'governorate_id' => $otherGovernorate->id,
        ]);
        $category = Category::factory()->create();
        $old = $this->createProduct($store->id, $category->id, 'Old shirt');
        $old->update(['created_at' => now()->subDays(2)]);
        $new = $this->createProduct($store->id, $category->id, 'New shirt');
        $new->update(['created_at' => now()->subDay()]);
        $this->createProduct($otherStore->id, $category->id, 'Outside shirt');
        $this->actingAs($customer, 'api');

        $url = '/api/user/products?governorate_id='.$governorate->id.'&sort=newest&pagination=on&limit_per_page=1';
        $this->getJson($url)->assertOk()->assertJsonPath('total', 2)->assertJsonPath('data.0.id', $new->id);
        $this->getJson($url.'&page=2')->assertOk()->assertJsonPath('data.0.id', $old->id);
    }

    public function test_user_can_read_published_product_details_with_store_policy_reviews_and_similar_products(): void
    {
        [$store, $customer, $policy] = $this->createStoreAndCustomer();
        $policy->update(['pickup_hold_hours' => 36, 'return_window_days' => 7, 'returns_enabled' => true, 'return_terms' => 'Seven days']);
        $category = Category::factory()->create();
        $product = $this->createProduct($store->id, $category->id, 'Leather jacket');
        $similar = $this->createProduct($store->id, $category->id, 'Cotton shirt');
        ProductFeature::query()->create(['product_id' => $product->id, 'name' => 'Natural leather']);
        ProductMedia::query()->create(['product_id' => $product->id, 'type' => MediaTypeEnum::Image, 'path' => 'products/jacket.jpg', 'is_primary' => true]);
        Favorite::query()->create(['user_id' => $customer->id, 'type' => FavoriteTypeEnum::Product, 'favoriteable_id' => $product->id]);
        $this->recordReview($store->id, $customer->id, $policy->id, $product, 5);

        $this->actingAs($customer, 'api')->getJson('/api/user/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.store.id', $store->id)
            ->assertJsonPath('data.policy.pickup_hold_hours', 36)
            ->assertJsonPath('data.policy.return_terms', 'Seven days')
            ->assertJsonPath('data.features.0', 'Natural leather')
            ->assertJsonPath('data.media.0.type', MediaTypeEnum::Image->value)
            ->assertJsonPath('data.is_favorite', true)
            ->assertJsonPath('data.average_rating', 5)
            ->assertJsonPath('data.reviews_count', 1)
            ->assertJsonPath('data.latest_review.rating', 5)
            ->assertJsonPath('data.similar_products.0.id', $similar->id);
    }

    public function test_product_details_require_login_and_do_not_expose_hidden_or_inactive_store_products(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $category = Category::factory()->create();
        $product = $this->createProduct($store->id, $category->id, 'Hidden jacket');
        $this->getJson('/api/user/products/'.$product->id)->assertUnauthorized();

        $this->actingAs($customer, 'api');
        $product->update(['status' => ProductStatusEnum::Draft]);
        $this->getJson('/api/user/products/'.$product->id)->assertNotFound();
        $product->update(['status' => ProductStatusEnum::Published]);
        $store->update(['status' => UserStatusEnum::Suspended]);
        $this->getJson('/api/user/products/'.$product->id)->assertNotFound();
        $this->withHeader('Accept-Language', 'ar')->getJson('/api/user/products/0')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_product_details.product.min'));
    }

    public function test_category_detail_preview_uses_products_endpoint_for_both_lists(): void
    {
        $preview = file_get_contents(base_path('Figma/screens/24-category-details.html'));

        $this->assertIsString($preview);
        $this->assertStringContainsString('products?${params}', $preview);
        $this->assertStringContainsString('sort,', $preview);
        $this->assertStringContainsString('main_category_id', $preview);
        $this->assertStringContainsString('data-show-more=', $preview);
        $this->assertStringContainsString('is_favorite', $preview);
        $this->assertStringContainsString("request('favorites/toggle'", $preview);
        $this->assertStringContainsString('saey_api_token', $preview);
    }

    public function test_user_can_toggle_product_and_store_favorites_through_one_endpoint(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $category = Category::factory()->create();
        $product = $this->createProduct($store->id, $category->id, 'Favorite product');
        $this->actingAs($customer, 'api');

        $this->getJson('/api/user/products?main_category_id='.$category->id)
            ->assertOk()
            ->assertJsonPath('data.0.is_favorite', false);

        $this->postJson('/api/user/favorites/toggle', [
            'type' => FavoriteTypeEnum::Product->value,
            'favoriteable_id' => $product->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.type', FavoriteTypeEnum::Product->value)
            ->assertJsonPath('data.favoriteable_id', $product->id)
            ->assertJsonPath('data.is_favorite', true)
            ->assertJsonPath('message', __('messages.user_favorites.added'));

        $this->assertSame(1, Favorite::query()
            ->where('user_id', $customer->id)
            ->where('type', FavoriteTypeEnum::Product)
            ->where('favoriteable_id', $product->id)
            ->count());

        $this->getJson('/api/user/products?main_category_id='.$category->id)
            ->assertOk()
            ->assertJsonPath('data.0.is_favorite', true);

        $otherCustomer = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
        $this->actingAs($otherCustomer, 'api')
            ->getJson('/api/user/products?main_category_id='.$category->id)
            ->assertOk()
            ->assertJsonPath('data.0.is_favorite', false);

        $this->actingAs($customer, 'api');

        $this->postJson('/api/user/favorites/toggle', [
            'type' => FavoriteTypeEnum::Product->value,
            'favoriteable_id' => $product->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_favorite', false)
            ->assertJsonPath('message', __('messages.user_favorites.removed'));

        $storeRecord = Store::query()->create([
            'owner_id' => $store->id,
            'category_id' => $category->id,
            'description' => 'Favorite store',
        ]);

        $this->postJson('/api/user/favorites/toggle', [
            'type' => FavoriteTypeEnum::Store->value,
            'favoriteable_id' => $storeRecord->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.type', FavoriteTypeEnum::Store->value)
            ->assertJsonPath('data.is_favorite', true);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $customer->id,
            'type' => FavoriteTypeEnum::Store->value,
            'favoriteable_id' => $storeRecord->id,
        ]);
    }

    public function test_favorite_toggle_requires_authentication_and_validates_type_and_item(): void
    {
        $this->getJson('/api/user/products')->assertUnauthorized();
        $this->postJson('/api/user/favorites/toggle', [])->assertUnauthorized();

        [, $customer] = $this->createStoreAndCustomer();
        $this->actingAs($customer, 'api');

        $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/user/favorites/toggle', ['type' => 'other', 'favoriteable_id' => 0])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Favorite type must be product or store.');

        $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/user/favorites/toggle', ['type' => 'product', 'favoriteable_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'المنتج أو المتجر المحدد غير موجود.');
    }

    private function createStoreAndCustomer(): array
    {
        $store = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $customer = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
        $policy = StorePolicyVersion::query()->create([
            'store_id' => $store->id,
            'version' => 1,
            'effective_at' => now(),
        ]);

        return [$store, $customer, $policy];
    }

    private function createProduct(int $storeId, int $categoryId, string $name): Product
    {
        return Product::query()->create([
            'store_id' => $storeId,
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'status' => ProductStatusEnum::Published,
            'price' => 100,
            'stock_quantity' => 3,
            'published_at' => now(),
        ]);
    }

    private function recordSale(
        int $storeId,
        int $customerId,
        int $policyId,
        Product $product,
        int $quantity,
        OrderStatusEnum $status = OrderStatusEnum::Completed,
    ): void {
        $order = $this->createOrder($storeId, $customerId, $policyId, $status);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => $product->price * $quantity,
        ]);
    }

    private function recordReview(int $storeId, int $customerId, int $policyId, Product $product, int $rating): void
    {
        $order = $this->createOrder($storeId, $customerId, $policyId);
        Review::query()->create([
            'order_id' => $order->id,
            'user_id' => $customerId,
            'store_id' => $storeId,
            'product_id' => $product->id,
            'rating' => $rating,
            'is_visible' => true,
        ]);
    }

    private function createOrder(
        int $storeId,
        int $customerId,
        int $policyId,
        OrderStatusEnum $status = OrderStatusEnum::Completed,
    ): Order {
        return Order::query()->create([
            'public_id' => (string) Str::uuid(),
            'number' => 'ORDER-'.Str::upper(Str::random(8)),
            'user_id' => $customerId,
            'store_id' => $storeId,
            'store_policy_version_id' => $policyId,
            'status' => $status,
            'payment_status' => PaymentStatusEnum::Paid,
            'subtotal' => 100,
            'total_amount' => 100,
            'policy_snapshot' => [],
        ]);
    }
}

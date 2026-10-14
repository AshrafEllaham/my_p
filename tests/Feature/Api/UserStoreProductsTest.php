<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Order;
use App\Models\Sai\OrderItem;
use App\Models\Sai\Product;
use App\Models\Sai\Review;
use App\Models\Sai\Store;
use App\Models\Sai\StorePolicyVersion;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserStoreProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_sort_and_paginate_visible_products_from_a_store(): void
    {
        [$store, $storeOwner, $customer, $policy] = $this->createStoreAndCustomer();
        $category = Category::factory()->create();
        $lessPurchased = $this->createProduct($storeOwner->id, $category->id, 'Cotton shirt');
        $mostPurchased = $this->createProduct($storeOwner->id, $category->id, 'Blue shirt');
        $unrated = $this->createProduct($storeOwner->id, $category->id, 'Green shirt');
        $newest = $this->createProduct($storeOwner->id, $category->id, 'Leather shoes');
        $lessPurchased->update(['created_at' => now()->subDays(3)]);
        $mostPurchased->update(['created_at' => now()->subDays(2)]);
        $unrated->update(['created_at' => now()->subDay()]);
        $this->recordSale($storeOwner->id, $customer->id, $policy->id, $lessPurchased, 2);
        $this->recordSale($storeOwner->id, $customer->id, $policy->id, $mostPurchased, 10);
        $this->recordSale($storeOwner->id, $customer->id, $policy->id, $lessPurchased, 100, OrderStatusEnum::Cancelled);
        $this->recordReview($storeOwner->id, $customer->id, $policy->id, $lessPurchased, 3);
        $this->recordReview($storeOwner->id, $customer->id, $policy->id, $mostPurchased, 5);
        $this->createProductForAnotherStore($category, 'Outside shirt');
        $this->actingAs($customer, 'api');

        $url = '/api/user/stores/'.$store->id.'/products?search=shirt&sort=most_purchased&limit_per_page=1';
        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('message', __('messages.user_stores.products_listed'))
            ->assertJsonPath('total', 3)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('data.0.id', $mostPurchased->id)
            ->assertJsonPath('data.0.units_sold', 10);

        $this->getJson($url.'&page=2')
            ->assertOk()
            ->assertJsonPath('data.0.id', $lessPurchased->id);

        $this->getJson('/api/user/stores/'.$store->id.'/products?search=shirt&sort=high_rate&limit_per_page=10')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.id', $mostPurchased->id)
            ->assertJsonPath('data.0.average_rating', 5)
            ->assertJsonPath('data.1.id', $lessPurchased->id);

        $this->getJson('/api/user/stores/'.$store->id.'/products?sort=newest')
            ->assertOk()
            ->assertJsonPath('data.0.id', $newest->id);
    }

    public function test_store_product_list_requires_auth_validates_filters_and_hides_unavailable_stores(): void
    {
        [$store, $storeOwner, $customer] = $this->createStoreAndCustomer();
        $url = '/api/user/stores/'.$store->id.'/products';
        $this->getJson($url)->assertUnauthorized();
        $this->actingAs($customer, 'api');

        $this->withHeader('Accept-Language', 'en')
            ->getJson($url.'?sort=top_selling')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Store product order must be high_rate, newest, or most_purchased.');
        $this->withHeader('Accept-Language', 'ar')
            ->getJson($url.'?search='.str_repeat('x', 121))
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_product_filters.search.max'));
        $this->getJson('/api/user/stores/0/products')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_store_details.store.min'));
        $this->getJson('/api/user/stores/999999/products')->assertNotFound();

        $storeOwner->update(['status' => UserStatusEnum::Suspended]);
        $this->getJson($url)->assertNotFound();
    }

    public function test_store_details_preview_loads_products_from_paginated_store_endpoint(): void
    {
        $preview = file_get_contents(base_path('Figma/screens/19-store-details.html'));
        $script = file_get_contents(base_path('Figma/screens/user-store-details.js'));

        $this->assertIsString($preview);
        $this->assertIsString($script);
        $this->assertStringContainsString('id="storeProductSort"', $preview);
        $this->assertStringContainsString('id="loadMoreProducts"', $preview);
        $this->assertStringContainsString('/api/user/stores/${encodeURIComponent(storeId)}/products', $script);
        $this->assertStringContainsString("query.set('search'", $script);
        $this->assertStringContainsString('sort: productSort.value', $script);
        $this->assertStringContainsString('page: String(requestedPage)', $script);
    }

    private function createStoreAndCustomer(): array
    {
        $storeOwner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $customer = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
        $category = Category::factory()->create();
        $store = Store::query()->create([
            'owner_id' => $storeOwner->id,
            'category_id' => $category->id,
            'description' => 'Store description',
        ]);
        $policy = StorePolicyVersion::query()->create([
            'store_id' => $storeOwner->id,
            'version' => 1,
            'effective_at' => now(),
        ]);

        return [$store, $storeOwner, $customer, $policy];
    }

    private function createProduct(int $storeOwnerId, int $categoryId, string $name): Product
    {
        return Product::query()->create([
            'store_id' => $storeOwnerId,
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

    private function createProductForAnotherStore(Category $category, string $name): Product
    {
        $otherStore = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);

        return $this->createProduct($otherStore->id, $category->id, $name);
    }

    private function recordSale(
        int $storeId,
        int $customerId,
        int $policyId,
        Product $product,
        int $quantity,
        OrderStatusEnum $status = OrderStatusEnum::Completed,
    ): void {
        $order = Order::query()->create([
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
        $order = Order::query()->create([
            'public_id' => (string) Str::uuid(),
            'number' => 'ORDER-'.Str::upper(Str::random(8)),
            'user_id' => $customerId,
            'store_id' => $storeId,
            'store_policy_version_id' => $policyId,
            'status' => OrderStatusEnum::Completed,
            'payment_status' => PaymentStatusEnum::Paid,
            'subtotal' => 100,
            'total_amount' => 100,
            'policy_snapshot' => [],
        ]);
        Review::query()->create([
            'order_id' => $order->id,
            'user_id' => $customerId,
            'store_id' => $storeId,
            'product_id' => $product->id,
            'rating' => $rating,
            'is_visible' => true,
        ]);
    }
}

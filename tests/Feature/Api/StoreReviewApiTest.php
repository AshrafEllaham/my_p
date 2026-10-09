<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Order;
use App\Models\Sai\Product;
use App\Models\Sai\Review;
use App\Models\Sai\StorePolicyVersion;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_get_own_product_reviews_paginated_with_filters_and_summary(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStore = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $customer = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $product = $this->createProduct($store->id, 'Leather jacket');
        $otherProduct = $this->createProduct($otherStore->id, 'Other product');
        $policy = StorePolicyVersion::query()->create([
            'store_id' => $store->id,
            'version' => 1,
            'effective_at' => now(),
        ]);
        $otherPolicy = StorePolicyVersion::query()->create([
            'store_id' => $otherStore->id,
            'version' => 1,
            'effective_at' => now(),
        ]);

        $this->createReview($store->id, $customer->id, $product, $policy->id, 5, 'Great quality.');
        $this->createReview($store->id, $customer->id, $product, $policy->id, 4, 'Good quality.');
        $this->createReview($store->id, $customer->id, $product, $policy->id, 1, 'Hidden review.', false);
        $this->createReview($otherStore->id, $customer->id, $otherProduct, $otherPolicy->id, 5, 'Another store review.');

        $this->withToken($this->tokenFor($store))
            ->getJson('/api/store/products/'.$product->id.'/reviews?limit_per_page=1&page=1&rating=5&period=all')
            ->assertOk()
            ->assertJsonPath('message', __('messages.reviews.listed'))
            ->assertJsonPath('total', 1)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('summary.total_reviews', 2)
            ->assertJsonPath('summary.average_rating', 4.5)
            ->assertJsonPath('summary.customer_satisfaction_percentage', 100)
            ->assertJsonPath('summary.rating_distribution.0.total', 0)
            ->assertJsonPath('summary.rating_distribution.3.total', 1)
            ->assertJsonPath('summary.rating_distribution.4.total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rating', 5)
            ->assertJsonPath('data.0.customer.name', $customer->name)
            ->assertJsonPath('data.0.product.name', 'Leather jacket');

        $this->withToken($this->tokenFor($store))
            ->getJson('/api/store/products/'.$product->id.'/reviews?period=week&rating=4')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.rating', 4);

        $this->withToken($this->tokenFor($store))
            ->getJson('/api/store/products/'.$otherProduct->id.'/reviews')
            ->assertNotFound();
    }

    public function test_store_reviews_endpoint_requires_authentication_and_store_account(): void
    {
        $this->getJson('/api/store/products/1/reviews')->assertUnauthorized();

        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $this->withToken($this->tokenFor($user))
            ->getJson('/api/store/products/1/reviews')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.profile.store_type_required'));
    }

    public function test_store_review_filter_validation_is_localized(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $token = $this->tokenFor($store);

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->getJson('/api/store/products/1/reviews?rating=6')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The review rating must be from 1 to 5.');

        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->getJson('/api/store/products/1/reviews?period=year')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'فترة التقييم المحددة غير متاحة.');
    }

    public function test_merchant_product_reviews_preview_loads_the_paginated_reviews_endpoint(): void
    {
        $preview = file_get_contents(base_path('Figma/screens/20-distributor-reviews.html'));

        $this->assertIsString($preview);
        $this->assertStringContainsString('fetch(`/api/store/products/${encodeURIComponent(productId)}/reviews?${query}`', $preview);
        $this->assertStringContainsString("query.set('rating', currentStars)", $preview);
        $this->assertStringContainsString('period:currentPeriod', $preview);
        $this->assertStringContainsString('updateSummary(result.summary || {})', $preview);
        $this->assertStringContainsString('renderPagination()', $preview);
    }

    private function createProduct(int $storeId, string $name): Product
    {
        $category = Category::factory()->create(['is_active' => true]);

        return Product::query()->create([
            'store_id' => $storeId,
            'category_id' => $category->id,
            'name' => $name,
            'description' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'status' => ProductStatusEnum::Published,
            'price' => 100,
            'stock_quantity' => 3,
            'published_at' => now(),
        ]);
    }

    private function createReview(int $storeId, int $customerId, Product $product, int $policyId, int $rating, string $comment, bool $visible = true): Review
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

        return Review::query()->create([
            'order_id' => $order->id,
            'user_id' => $customerId,
            'store_id' => $storeId,
            'product_id' => $product->id,
            'rating' => $rating,
            'comment' => $comment,
            'is_visible' => $visible,
        ]);
    }

    private function tokenFor(object $user): string
    {
        return (string) auth('api')->login($user);
    }
}

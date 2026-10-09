<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Order;
use App\Models\Sai\OrderItem;
use App\Models\Sai\Product;
use App\Models\Sai\ProductFeature;
use App\Models\Sai\Review;
use App\Models\Sai\StorePolicyVersion;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_store_owner_can_create_published_product_with_features_and_images(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->withToken($this->tokenFor($store))->post('/api/store/products', [
            'name' => 'Leather Jacket',
            'category_id' => $category->id,
            'description' => 'A natural leather jacket.',
            'features' => ['Natural leather', 'Comfortable lining'],
            'price' => '1850.00',
            'stock_quantity' => 18,
            'discount_percentage' => 10,
            'discount_ends_at' => now()->addDays(10)->toDateTimeString(),
            'images' => [UploadedFile::fake()->image('jacket.jpg')],
            'publish' => true,
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('message', __('messages.products.created'))
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.category_id', $category->id)
            ->assertJsonPath('data.status', ProductStatusEnum::Published->value)
            ->assertJsonPath('data.stock_quantity', 18)
            ->assertJsonPath('data.features.0', 'Natural leather')
            ->assertJsonPath('data.media.0.is_primary', true);

        $productId = $response->json('data.id');
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'store_id' => $store->id,
            'category_id' => $category->id,
            'status' => ProductStatusEnum::Published->value,
            'discount_percentage' => 10,
        ]);
        $this->assertDatabaseCount('product_features', 2);
        $this->assertDatabaseCount('product_media', 1);
    }

    public function test_store_product_endpoint_requires_authentication_and_store_account(): void
    {
        $this->postJson('/api/store/products')->assertUnauthorized();

        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $category = Category::factory()->create(['is_active' => true]);

        $this->withToken($this->tokenFor($user))->postJson('/api/store/products', [
            'name' => 'Product',
            'category_id' => $category->id,
            'description' => 'Description',
            'price' => 10,
            'stock_quantity' => 1,
        ])->assertUnprocessable()
            ->assertJsonPath('message', __('messages.profile.store_type_required'));
    }

    public function test_product_validation_messages_are_localized_in_arabic_and_english(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $token = $this->tokenFor($store);

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->postJson('/api/store/products', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The product name is required.');

        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->postJson('/api/store/products', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'اسم المنتج مطلوب.');
    }

    public function test_store_can_create_product_as_draft(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->withToken($this->tokenFor($store))->postJson('/api/store/products', [
            'name' => 'Draft product',
            'category_id' => $category->id,
            'description' => 'Saved for later.',
            'price' => 50,
            'stock_quantity' => 0,
            'publish' => false,
        ]);

        $response->assertOk()->assertJsonPath('data.status', ProductStatusEnum::Draft->value)
            ->assertJsonPath('data.published_at', null);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_store_owner_can_list_only_own_products_with_pagination(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStore = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);
        $first = $this->createProduct($store->id, $category->id);
        $second = $this->createProduct($store->id, $category->id);
        $this->createProduct($otherStore->id, $category->id);

        $token = $this->tokenFor($store);
        $this->withToken($token)->getJson('/api/store/products?limit_per_page=1&page=1')
            ->assertOk()
            ->assertJsonPath('message', __('messages.products.listed'))
            ->assertJsonPath('total', 2)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('current_page', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.store_id', $store->id);

        $this->withToken($token)->getJson('/api/store/products?limit_per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $first->id);
        $this->assertNotSame($first->id, $second->id);
    }

    public function test_store_owner_can_get_product_details_and_database_backed_analytics(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->createProduct($store->id, $category->id, [
            'description' => 'Product details description.',
            'sku' => 'SKU-DETAILS',
        ]);
        ProductFeature::query()->create(['product_id' => $product->id, 'name' => 'Lightweight']);
        $customer = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $policy = StorePolicyVersion::query()->create([
            'store_id' => $store->id,
            'version' => 1,
            'effective_at' => now(),
        ]);
        $order = Order::query()->create([
            'public_id' => (string) Str::uuid(),
            'number' => 'ORDER-'.Str::upper(Str::random(8)),
            'user_id' => $customer->id,
            'store_id' => $store->id,
            'store_policy_version_id' => $policy->id,
            'status' => OrderStatusEnum::Completed,
            'payment_status' => PaymentStatusEnum::Paid,
            'subtotal' => 215,
            'total_amount' => 215,
            'policy_snapshot' => [],
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 107.5,
            'quantity' => 2,
            'line_total' => 215,
        ]);
        Review::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'store_id' => $store->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Excellent product.',
            'is_visible' => true,
        ]);

        $this->withHeader('Accept-Language', 'en')
            ->withToken($this->tokenFor($store))
            ->getJson('/api/store/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('message', 'Product details were retrieved successfully.')
            ->assertJsonPath('data.product.id', $product->id)
            ->assertJsonPath('data.product.sku', 'SKU-DETAILS')
            ->assertJsonPath('data.product.category.id', $category->id)
            ->assertJsonPath('data.product.features.0', 'Lightweight')
            ->assertJsonPath('data.analytics.sales.total_revenue', 215)
            ->assertJsonPath('data.analytics.sales.completed_orders_count', 1)
            ->assertJsonPath('data.analytics.sales.units_sold', 2)
            ->assertJsonPath('data.analytics.sales.revenue_last_30_days', 215)
            ->assertJsonCount(30, 'data.analytics.sales.daily_sales')
            ->assertJsonPath('data.analytics.sales.daily_sales.29.revenue', 215)
            ->assertJsonPath('data.analytics.ads.impressions', 0)
            ->assertJsonPath('data.analytics.ads.conversion_rate', null)
            ->assertJsonPath('data.analytics.reviews.total_reviews', 1)
            ->assertJsonPath('data.analytics.reviews.average_rating', 5)
            ->assertJsonPath('data.analytics.reviews.rating_distribution', [
                ['rating' => 1, 'total' => 0],
                ['rating' => 2, 'total' => 0],
                ['rating' => 3, 'total' => 0],
                ['rating' => 4, 'total' => 0],
                ['rating' => 5, 'total' => 1],
            ])
            ->assertJsonPath('data.analytics.reviews.latest.user_name', $customer->name)
            ->assertJsonPath('data.analytics.reviews.latest.comment', 'Excellent product.');
    }

    public function test_product_details_are_private_to_the_owning_store(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStore = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $regularUser = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->createProduct($otherStore->id, $category->id);

        $this->getJson('/api/store/products/'.$product->id)->assertUnauthorized();

        $this->withToken($this->tokenFor($store))
            ->getJson('/api/store/products/'.$product->id)
            ->assertNotFound();

        $this->withToken($this->tokenFor($regularUser))
            ->getJson('/api/store/products/'.$product->id)
            ->assertUnprocessable();

        $this->withToken($this->tokenFor($store))
            ->getJson('/api/store/products/999999')
            ->assertNotFound();
    }

    public function test_store_product_list_returns_unfiltered_store_summary_counts(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStore = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);
        $this->createProduct($store->id, $category->id, [
            'status' => ProductStatusEnum::Published,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);
        $this->createProduct($store->id, $category->id, [
            'status' => ProductStatusEnum::Draft,
            'stock_quantity' => 10,
        ]);
        $oldProduct = $this->createProduct($store->id, $category->id, [
            'status' => ProductStatusEnum::Published,
            'stock_quantity' => 10,
        ]);
        $oldProduct->created_at = now()->startOfMonth()->subSecond();
        $oldProduct->save();
        $deletedProduct = $this->createProduct($store->id, $category->id);
        $deletedProduct->delete();
        $this->createProduct($otherStore->id, $category->id);

        $this->withToken($this->tokenFor($store))->getJson('/api/store/products?status=draft')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('total_products_count', 3)
            ->assertJsonPath('total_new_products_this_month_count', 2)
            ->assertJsonPath('total_published_products_count', 2)
            ->assertJsonPath('low_stock_products_count', 1);
    }

    public function test_store_product_list_supports_status_category_search_stock_and_sort_filters(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $parent = Category::factory()->create();
        $subcategory = Category::factory()->subcategory($parent)->create([
            'ar' => ['name' => 'ملابس رجالي', 'description' => null],
            'en' => ['name' => 'Men Clothing', 'description' => null],
        ]);
        $otherSubcategory = Category::factory()->subcategory($parent)->create();
        $published = $this->createProduct($store->id, $subcategory->id, [
            'name' => 'Leather Jacket',
            'stock_quantity' => 12,
            'low_stock_threshold' => 5,
        ]);
        $draftLowStock = $this->createProduct($store->id, $subcategory->id, [
            'name' => 'Leather Vest',
            'status' => ProductStatusEnum::Draft,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);
        $outOfStock = $this->createProduct($store->id, $otherSubcategory->id, [
            'name' => 'Blue Shoes',
            'stock_quantity' => 0,
        ]);
        $token = $this->tokenFor($store);

        $this->withToken($token)->getJson('/api/store/products?status=draft')
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $draftLowStock->id);
        $this->withHeader('Accept-Language', 'ar')->withToken($token)->getJson('/api/store/products?category_id='.$subcategory->id)
            ->assertOk()->assertJsonPath('total', 2)->assertJsonPath('data.0.category.name', 'ملابس رجالي');
        $this->withHeader('Accept-Language', 'ar')->withToken($token)->getJson('/api/store/products?search=ملابس%20رجالي')
            ->assertOk()->assertJsonPath('total', 2);
        $this->withToken($token)->getJson('/api/store/products?search=Leather')
            ->assertOk()->assertJsonPath('total', 2);
        $this->withToken($token)->getJson('/api/store/products?stock_status=available')
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $published->id);
        $this->withToken($token)->getJson('/api/store/products?stock_status=low')
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $draftLowStock->id);
        $this->withToken($token)->getJson('/api/store/products?stock_status=out')
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $outOfStock->id);

        foreach (['recent', 'orders', 'views'] as $orderBy) {
            $this->withToken($token)->getJson('/api/store/products?order_by='.$orderBy)
                ->assertOk()
                ->assertJsonPath('total', 3);
        }

        $this->assertNotSame($published->id, $draftLowStock->id);
    }

    public function test_store_product_filter_validation_is_translated(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $parent = Category::factory()->create();
        $token = $this->tokenFor($store);

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->getJson('/api/store/products?status=unknown')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The selected product status is unavailable.');
        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->getJson('/api/store/products?category_id='.$parent->id)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The selected active subcategory was not found.');
        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->getJson('/api/store/products?order_by=popular')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The selected product order is unavailable.');
        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->getJson('/api/store/products?search='.str_repeat('a', 256))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The search text is too long.');
        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->getJson('/api/store/products?stock_status=empty')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'حالة المخزون المحددة غير متاحة.');
    }

    public function test_product_list_requires_store_auth_and_valid_page_parameters(): void
    {
        $this->getJson('/api/store/products')->assertUnauthorized();

        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $this->withToken($this->tokenFor($user))->getJson('/api/store/products')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.profile.store_type_required'));

        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $token = $this->tokenFor($store);
        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->getJson('/api/store/products?page=0')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The page number must be at least 1.');

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->getJson('/api/store/products?limit_per_page=101')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The items per page value must not exceed 100.');

        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->getJson('/api/store/products?page=0')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'رقم الصفحة يجب أن يبدأ من 1.');
    }

    public function test_add_product_screen_submits_to_the_store_product_endpoint(): void
    {
        $screen = file_get_contents(base_path('Figma/screens/18-add-product.html'));

        $this->assertIsString($screen);
        $this->assertStringContainsString("'/api/store/products'", $screen);
        $this->assertStringContainsString("appendIfFilled('category_id'", $screen);
        $this->assertStringContainsString("payload.append('images[]'", $screen);
    }

    public function test_store_owner_can_update_product_fields_and_features(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->createProduct($store->id, $category->id);

        $this->withToken($this->tokenFor($store))->post('/api/store/products/'.$product->id, [
            '_method' => 'PATCH',
            'name' => 'Updated product',
            'price' => 125,
            'features' => ['Updated feature'],
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('message', __('messages.products.updated'))
            ->assertJsonPath('data.name', 'Updated product')
            ->assertJsonPath('data.price', '125.00')
            ->assertJsonPath('data.features.0', 'Updated feature');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated product']);
        $this->assertDatabaseCount('product_features', 1);
    }

    public function test_store_owner_can_toggle_product_between_hidden_and_published(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->createProduct($store->id, $category->id);
        $token = $this->tokenFor($store);

        $this->withToken($token)->patchJson('/api/store/products/'.$product->id.'/hide')
            ->assertOk()
            ->assertJsonPath('message', __('messages.products.visibility_toggled'))
            ->assertJsonPath('data.status', ProductStatusEnum::Hidden->value);

        $this->withToken($token)->patchJson('/api/store/products/'.$product->id.'/hide')
            ->assertOk()
            ->assertJsonPath('message', __('messages.products.visibility_toggled'))
            ->assertJsonPath('data.status', ProductStatusEnum::Published->value);
    }

    public function test_store_owner_can_soft_delete_product(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->createProduct($store->id, $category->id);

        $this->withToken($this->tokenFor($store))->deleteJson('/api/store/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('message', __('messages.products.deleted'))
            ->assertJsonPath('data', null);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_store_owner_cannot_mutate_another_stores_product(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStore = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->createProduct($otherStore->id, $category->id);

        $this->withToken($this->tokenFor($owner))->patchJson('/api/store/products/'.$product->id, [
            'name' => 'Stolen update',
        ])->assertNotFound();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => $product->name]);
    }

    public function test_product_update_validation_message_is_translated(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create(['is_active' => true]);
        $product = $this->createProduct($store->id, $category->id);

        $this->withHeader('Accept-Language', 'en')
            ->withToken($this->tokenFor($store))
            ->patchJson('/api/store/products/'.$product->id, ['price' => 0])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The price must be greater than zero.');
    }

    public function test_preview_product_actions_call_their_api_endpoints(): void
    {
        $list = file_get_contents(base_path('Figma/screens/18-distributor-products.html'));
        $details = file_get_contents(base_path('Figma/screens/18-distributor-product-details.html'));
        $editor = file_get_contents(base_path('Figma/screens/18-add-product.html'));

        $this->assertIsString($list);
        $this->assertIsString($details);
        $this->assertIsString($editor);
        $this->assertStringContainsString('fetch(`/api/store/products?${query.toString()}`', $list);
        $this->assertStringContainsString('filters.category_id = categoryId', $list);
        $this->assertStringContainsString('filters.stock_status = stockStatus', $list);
        $this->assertStringContainsString('filters.order_by = orderBy', $list);
        $this->assertStringContainsString('loadSubcategories()', $list);
        $this->assertStringContainsString('productListPagination', $list);
        $this->assertStringContainsString('result.total_products_count', $list);
        $this->assertStringContainsString('result.total_new_products_this_month_count', $list);
        $this->assertStringContainsString('result.total_published_products_count', $list);
        $this->assertStringContainsString('result.low_stock_products_count', $list);
        $this->assertStringContainsString("storeProductRequest(productId, 'DELETE')", $list);
        $this->assertStringContainsString("storeProductRequest(productId, 'PATCH', '/hide')", $list);
        $this->assertStringContainsString("productActionRequest('PATCH', '/hide')", $details);
        $this->assertStringContainsString("addEventListener('click', toggleProductVisibility)", $details);
        $this->assertStringContainsString("product.status === 'hidden'", $list);
        $this->assertStringContainsString("result.data.status === 'hidden'", $details);
        $this->assertStringContainsString("fetch('/api/store/products/' + encodeURIComponent(productId)", $details);
        $this->assertStringContainsString('setProductDetails(result.data)', $details);
        $this->assertStringNotContainsString('const products = {', $details);
        $this->assertStringContainsString("payload.append('_method', 'PATCH')", $editor);
    }

    private function createProduct(int $storeId, int $categoryId, array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'store_id' => $storeId,
            'category_id' => $categoryId,
            'name' => 'Original product',
            'description' => 'Original description',
            'slug' => 'original-product-'.uniqid(),
            'status' => ProductStatusEnum::Published,
            'price' => 100,
            'stock_quantity' => 5,
            'published_at' => now(),
        ], $attributes));
    }

    private function tokenFor(object $user): string
    {
        return (string) auth('api')->login($user);
    }
}

<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\DisputeStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ReturnResolutionTypeEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Order;
use App\Models\Sai\Product;
use App\Models\Sai\Store;
use App\Models\Sai\StorePolicyVersion;
use App\Models\Sai\User;
use App\Models\Sai\Wallet;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_checkout_paginate_read_get_receipt_and_cancel_with_refund(): void
    {
        [$user, $storeOwner, $product] = $this->orderFixture();
        $token = $this->tokenFor($user);

        $firstPayload = $this->checkoutPayload($product, 2);
        $firstOrder = $this->withToken($token)->postJson('/api/user/orders', $firstPayload)
            ->assertOk()
            ->assertJsonPath('message', __('messages.orders.created'))
            ->assertJsonPath('data.status', OrderStatusEnum::PendingStore->value)
            ->assertJsonPath('data.payment_status', PaymentStatusEnum::Paid->value)
            ->assertJsonPath('data.total_amount', '251.00')
            ->json('data');

        $secondOrder = $this->withToken($token)->postJson('/api/user/orders', $this->checkoutPayload($product, 1))
            ->assertOk()
            ->assertJsonPath('data.total_amount', '125.50')
            ->json('data');

        $this->withToken($token)->postJson('/api/user/orders', $firstPayload)
            ->assertOk()
            ->assertJsonPath('data.id', $firstOrder['id']);

        $this->withToken($token)->getJson('/api/user/orders?page=1&limit_per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2);

        $this->withToken($token)->getJson('/api/user/orders?page=2&limit_per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('total', 2);

        $this->withToken($token)->getJson('/api/user/orders/'.$firstOrder['id'])
            ->assertOk()
            ->assertJsonPath('data.id', $firstOrder['id'])
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.store.id', $storeOwner->id);

        $this->withToken($token)->getJson('/api/user/orders/'.$firstOrder['id'].'/receipt')
            ->assertOk()
            ->assertJsonPath('data.order_id', $firstOrder['id'])
            ->assertJsonPath('data.snapshot.total_amount', '251.00')
            ->assertJsonPath('data.snapshot.payment_method', 'wallet');

        $this->withToken($token)->patchJson('/api/user/orders/'.$firstOrder['id'].'/cancel', [
            'reason' => 'Changed my mind',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatusEnum::Cancelled->value)
            ->assertJsonPath('data.payment_status', PaymentStatusEnum::Refunded->value);

        $this->withToken($token)->patchJson('/api/user/orders/'.$firstOrder['id'].'/cancel')
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatusEnum::Cancelled->value);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'available_balance' => '874.50',
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 9]);
        $this->assertDatabaseCount('refunds', 1);

        $this->withToken($token)->getJson('/api/user/orders?status=cancelled')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $firstOrder['id']);

        $otherUser = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $this->withToken($this->tokenFor($otherUser))->getJson('/api/user/orders/'.$secondOrder['id'])
            ->assertNotFound();
    }

    public function test_completed_orders_support_review_return_and_exchange_requests(): void
    {
        [$user, , $product] = $this->orderFixture();
        $token = $this->tokenFor($user);
        $completedOrders = [];

        foreach (range(1, 3) as $_) {
            $response = $this->withToken($token)->postJson('/api/user/orders', $this->checkoutPayload($product, 1))
                ->assertOk();
            $completedOrders[] = (int) $response->json('data.id');
        }

        Order::query()->whereIn('id', $completedOrders)->update([
            'status' => OrderStatusEnum::Completed,
            'completed_at' => now(),
        ]);

        $this->withToken($token)->postJson('/api/user/orders/'.$completedOrders[0].'/review', [
            'rating' => 5,
            'comment' => 'Good quality and fast pickup.',
        ])
            ->assertOk()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.comment', 'Good quality and fast pickup.');

        $this->withToken($token)->postJson('/api/user/orders/'.$completedOrders[0].'/review', [
            'rating' => 4,
        ])->assertUnprocessable();

        $this->withToken($token)->postJson('/api/user/orders/'.$completedOrders[1].'/return-requests', [
            'reason' => 'damaged_item',
            'details' => 'The product arrived with visible damage.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', ReturnStatusEnum::PendingReview->value)
            ->assertJsonPath('data.resolution_type', ReturnResolutionTypeEnum::Refund->value);

        $this->withToken($token)->postJson('/api/user/orders/'.$completedOrders[2].'/exchange-requests', [
            'reason' => 'wrong_size',
            'details' => 'Please exchange this item for a larger size.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', ReturnStatusEnum::PendingReview->value)
            ->assertJsonPath('data.resolution_type', ReturnResolutionTypeEnum::Exchange->value);

        $this->withToken($token)->getJson('/api/user/orders/'.$completedOrders[1])
            ->assertOk()
            ->assertJsonPath('data.return_request.resolution_type', ReturnResolutionTypeEnum::Refund->value);
    }

    public function test_user_can_confirm_completion_or_open_a_dispute_for_ready_order(): void
    {
        [$user, , $product] = $this->orderFixture();
        $token = $this->tokenFor($user);
        $orderIds = [];

        foreach (range(1, 2) as $_) {
            $orderIds[] = (int) $this->withToken($token)
                ->postJson('/api/user/orders', $this->checkoutPayload($product, 1))
                ->assertOk()
                ->json('data.id');
        }

        Order::query()->whereIn('id', $orderIds)->update([
            'status' => OrderStatusEnum::ReadyForCompletion,
            'ready_at' => now(),
            'customer_confirmation_requested_at' => now(),
        ]);

        $this->withToken($token)->postJson('/api/user/orders/'.$orderIds[0].'/disputes', [
            'reason' => 'customer_reported_issue',
            'details' => 'The product was not available at the store.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', DisputeStatusEnum::Open->value)
            ->assertJsonPath('data.reason', 'customer_reported_issue');

        $this->withToken($token)->getJson('/api/user/orders/'.$orderIds[0])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatusEnum::Disputed->value)
            ->assertJsonPath('data.dispute.status', DisputeStatusEnum::Open->value);

        $this->withToken($token)->postJson('/api/user/orders/'.$orderIds[0].'/disputes/messages', [
            'body' => 'I am sending additional details.',
        ])->assertUnprocessable();

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->patchJson('/api/user/orders/'.$orderIds[1].'/confirm-completion')
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatusEnum::Completed->value)
            ->assertJsonPath('message', __('messages.orders.customer_completion_confirmed'));

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->postJson('/api/user/orders/'.$orderIds[1].'/disputes', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.order_dispute.reason.required'));

        $this->assertDatabaseHas('orders', ['id' => $orderIds[0], 'status' => OrderStatusEnum::Disputed->value]);
        $this->assertDatabaseHas('orders', ['id' => $orderIds[1], 'status' => OrderStatusEnum::Completed->value]);
        $this->assertDatabaseCount('order_disputes', 1);
    }

    public function test_store_can_process_an_order_and_user_can_finish_the_order_flow(): void
    {
        [$user, $storeOwner, $product] = $this->orderFixture();
        $userToken = $this->tokenFor($user);

        $order = $this->withToken($userToken)->postJson('/api/user/orders', $this->checkoutPayload($product, 1))
            ->assertOk()
            ->json('data');

        $storeToken = $this->tokenFor($storeOwner);
        $this->app['auth']->forgetGuards();

        $this->withToken($storeToken)->getJson('/api/store/orders?pagination=on&limit_per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order['id'])
            ->assertJsonPath('data.0.customer.name', $user->name);

        $this->withToken($storeToken)->getJson('/api/store/orders/'.$order['id'])
            ->assertOk()
            ->assertJsonPath('data.items.0.product_name', $product->name);

        $this->withHeader('Accept-Language', 'en')->withToken($storeToken)
            ->patchJson('/api/store/orders/'.$order['id'].'/status', ['status' => OrderStatusEnum::ReadyForCompletion->value])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.orders.store_status_transition_unavailable'));

        foreach ([OrderStatusEnum::Confirmed, OrderStatusEnum::Preparing, OrderStatusEnum::ReadyForCompletion] as $status) {
            $this->withToken($storeToken)->patchJson('/api/store/orders/'.$order['id'].'/status', ['status' => $status->value])
                ->assertOk()
                ->assertJsonPath('data.status', $status->value);
        }

        $userToken = $this->tokenFor($user);
        $readyOrder = $this->withToken($userToken)->getJson('/api/user/orders/'.$order['id'])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatusEnum::ReadyForCompletion->value)
            ->json('data');
        $this->assertNotEmpty($readyOrder['pickup_qr_token']);

        $pickupToken = $readyOrder['pickup_qr_token'];
        $storeToken = $this->tokenFor($storeOwner);
        $this->withHeader('Accept-Language', 'en')->withToken($storeToken)
            ->postJson('/api/store/orders/'.$order['id'].'/confirm-pickup', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.store_pickup.pickup_qr_token.required'));

        $storeToken = $this->tokenFor($storeOwner);
        $this->withHeader('Accept-Language', 'en')->withToken($storeToken)
            ->postJson('/api/store/orders/'.$order['id'].'/confirm-pickup', ['pickup_qr_token' => '00000000-0000-4000-8000-000000000000'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.orders.pickup_code_invalid'));

        $storeToken = $this->tokenFor($storeOwner);
        $this->withToken($storeToken)->postJson('/api/store/orders/'.$order['id'].'/confirm-pickup', ['pickup_qr_token' => $pickupToken])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatusEnum::Completed->value);

        $this->tokenFor($user);
        $this->assertDatabaseHas('orders', ['id' => $order['id'], 'status' => OrderStatusEnum::Completed->value]);
    }

    public function test_order_routes_require_authentication_and_checkout_validation_is_localized(): void
    {
        $this->getJson('/api/user/orders')->assertUnauthorized();

        [$user] = $this->orderFixture();
        $token = $this->tokenFor($user);

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->postJson('/api/user/orders', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A product is required.');

        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->postJson('/api/user/orders', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'المنتج مطلوب.');
    }

    /** @return array{User, User, Product} */
    private function orderFixture(): array
    {
        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $storeOwner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $category = Category::query()->create(['is_active' => true, 'sort_order' => 0]);
        Store::query()->create(['owner_id' => $storeOwner->id, 'category_id' => $category->id]);

        $product = Product::query()->create([
            'store_id' => $storeOwner->id,
            'category_id' => $category->id,
            'name' => 'Test order product',
            'description' => 'Product used by the order feature flow test.',
            'slug' => 'test-order-product-'.Str::lower(Str::random(8)),
            'status' => ProductStatusEnum::Published,
            'price' => '125.50',
            'stock_quantity' => 10,
            'published_at' => now(),
        ]);

        StorePolicyVersion::query()->create([
            'store_id' => $storeOwner->id,
            'version' => 1,
            'pickup_hold_hours' => 24,
            'return_window_days' => 14,
            'returns_enabled' => true,
            'exchanges_enabled' => true,
            'cancellation_enabled' => true,
            'is_current' => true,
            'effective_at' => now(),
        ]);

        Wallet::query()->create([
            'user_id' => $user->id,
            'currency' => 'EGP',
            'available_balance' => '1000.00',
            'pending_balance' => '0.00',
            'is_active' => true,
        ]);

        return [$user, $storeOwner, $product];
    }

    /** @return array{product_id: int, quantity: int, idempotency_key: string} */
    private function checkoutPayload(Product $product, int $quantity): array
    {
        return [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'idempotency_key' => (string) Str::uuid(),
        ];
    }

    private function tokenFor(object $user): string
    {
        return (string) auth('api')->login($user);
    }
}

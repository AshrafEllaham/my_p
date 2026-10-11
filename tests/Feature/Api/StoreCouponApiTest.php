<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\CouponDiscountTypeEnum;
use App\Enums\CouponStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Coupon;
use App\Models\Sai\CouponRedemption;
use App\Models\Sai\Order;
use App\Models\Sai\Store;
use App\Models\Sai\StorePolicyVersion;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreCouponApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_create_list_edit_toggle_and_delete_its_coupons(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $token = $this->tokenFor($owner);

        $created = $this->withToken($token)->postJson('/api/store/coupons', $this->couponData())
            ->assertOk()
            ->assertJsonPath('message', __('messages.coupons.created'))
            ->assertJsonPath('data.store_id', $store->id)
            ->assertJsonPath('data.code', 'SAEY20')
            ->assertJsonPath('data.discount_type', CouponDiscountTypeEnum::Percentage->value)
            ->assertJsonPath('data.status', CouponStatusEnum::Active->value)
            ->assertJsonPath('data.is_active', true);
        $couponId = $created->json('data.id');

        $this->withToken($token)->patchJson("/api/store/coupons/{$couponId}", [
            'code' => 'SAEY25',
            'discount_value' => 25,
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.code', 'SAEY25')
            ->assertJsonPath('data.discount_value', '25.00')
            ->assertJsonPath('data.status', CouponStatusEnum::Paused->value)
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)->getJson('/api/store/coupons?pagination=off&search=SAEY')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $couponId);
        $this->withToken($token)->getJson("/api/store/coupons/{$couponId}")
            ->assertOk()->assertJsonPath('data.id', $couponId);
        $this->withToken($token)->patchJson("/api/store/coupons/{$couponId}/toggle")
            ->assertOk()->assertJsonPath('data.status', CouponStatusEnum::Active->value);

        $this->withToken($token)->deleteJson("/api/store/coupons/{$couponId}")
            ->assertOk()->assertJsonPath('message', __('messages.coupons.deleted'));
        $this->assertSoftDeleted('coupons', ['id' => $couponId]);
    }

    public function test_store_can_list_coupons_with_pagination_metadata(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $token = $this->tokenFor($owner);

        foreach (['FIRST10', 'SECOND10', 'THIRD10'] as $code) {
            Coupon::query()->create([
                'store_id' => $store->id,
                'code' => $code,
                'discount_type' => CouponDiscountTypeEnum::Percentage,
                'discount_value' => 10,
                'minimum_order_amount' => 0,
                'max_uses' => 10,
                'max_uses_per_user' => 1,
                'status' => CouponStatusEnum::Active,
                'expires_at' => now()->addYear(),
            ]);
        }

        $this->withToken($token)
            ->getJson('/api/store/coupons')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonMissingPath('current_page');

        $this->withToken($token)
            ->getJson('/api/store/coupons?pagination=on&page=2&limit_per_page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'FIRST10')
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('total', 3)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('summary.active_coupons_count', 3)
            ->assertJsonPath('summary.total_uses_count', 0);
    }

    public function test_store_can_filter_all_coupons_by_active_inactive_and_expired_status(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $token = $this->tokenFor($owner);

        $this->createCoupon($store->id, 'ACTIVE10', CouponStatusEnum::Active, now()->addYear(), 2);
        $this->createCoupon($store->id, 'INACTIVE10', CouponStatusEnum::Paused, now()->addYear(), 4);
        $this->createCoupon($store->id, 'EXPIRED10', CouponStatusEnum::Active, now()->subDay(), 5);
        $otherOwner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStore = Store::query()->create(['owner_id' => $otherOwner->id, 'category_id' => $category->id]);
        $this->createCoupon($otherStore->id, 'OTHER100', CouponStatusEnum::Active, now()->addYear(), 100);

        $this->withToken($token)->getJson('/api/store/coupons')
            ->assertOk()
            ->assertJsonPath('summary.active_coupons_count', 1)
            ->assertJsonPath('summary.total_uses_count', 11);

        $this->withToken($token)->getJson('/api/store/coupons?status=active')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'ACTIVE10');
        $this->withToken($token)->getJson('/api/store/coupons?status=inactive')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'INACTIVE10')
            ->assertJsonPath('summary.total_uses_count', 11);
        $this->withToken($token)->getJson('/api/store/coupons?status=expired')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'EXPIRED10');
    }

    public function test_store_cannot_read_another_stores_coupon(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherOwner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        Store::query()->create(['owner_id' => $otherOwner->id, 'category_id' => $category->id]);
        $coupon = Coupon::query()->create([
            'store_id' => Store::query()->where('owner_id', $owner->id)->value('id'),
            'code' => 'SAEY20',
            'discount_type' => CouponDiscountTypeEnum::Percentage,
            'discount_value' => 20,
            'minimum_order_amount' => 0,
            'max_uses' => 10,
            'max_uses_per_user' => 1,
            'status' => CouponStatusEnum::Active,
            'expires_at' => now()->addYear(),
        ]);

        $this->withToken($this->tokenFor($otherOwner))->getJson("/api/store/coupons/{$coupon->id}")
            ->assertNotFound();
    }

    public function test_coupon_details_include_database_backed_statistics(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $customer = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $coupon = $this->createCoupon($store->id, 'STATS20', CouponStatusEnum::Active, now()->addYear(), 4);
        $policy = StorePolicyVersion::query()->create([
            'store_id' => $owner->id,
            'version' => 1,
            'effective_at' => now(),
        ]);

        foreach ([
            ['status' => OrderStatusEnum::Completed, 'payment_status' => PaymentStatusEnum::Paid, 'total' => 800, 'discount' => 200],
            ['status' => OrderStatusEnum::Confirmed, 'payment_status' => PaymentStatusEnum::Paid, 'total' => 1200, 'discount' => 300],
        ] as $index => $data) {
            $order = Order::query()->create([
                'public_id' => (string) Str::uuid(),
                'number' => 'COUPON-'.Str::upper(Str::random(8)),
                'user_id' => $customer->id,
                'store_id' => $owner->id,
                'coupon_id' => $coupon->id,
                'store_policy_version_id' => $policy->id,
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'subtotal' => $data['total'] + $data['discount'],
                'discount_amount' => $data['discount'],
                'total_amount' => $data['total'],
                'policy_snapshot' => [],
            ]);

            $redemption = CouponRedemption::query()->create([
                'coupon_id' => $coupon->id,
                'user_id' => $customer->id,
                'order_id' => $order->id,
                'discount_amount' => $data['discount'],
            ]);
            $redemption->forceFill(['created_at' => now()->subDays($index * 3)])->save();
        }

        $this->withToken($this->tokenFor($owner))
            ->getJson('/api/store/coupons/'.$coupon->id)
            ->assertOk()
            ->assertJsonPath('data.id', $coupon->id)
            ->assertJsonPath('data.statistics.completed_orders_count', 1)
            ->assertJsonPath('data.statistics.sales_amount', 800)
            ->assertJsonPath('data.statistics.discount_amount', 200)
            ->assertJsonPath('data.statistics.average_order_amount', 800)
            ->assertJsonPath('data.statistics.usage_last_7_days.6.count', 1)
            ->assertJsonPath('data.statistics.usage_last_7_days.3.count', 1);
    }

    public function test_coupon_validation_messages_are_localized_in_arabic_and_english(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $token = $this->tokenFor($owner);

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->postJson('/api/store/coupons', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The coupon code is required.');

        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->postJson('/api/store/coupons', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'كود الكوبون مطلوب.');

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->getJson('/api/store/coupons?status=paused')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Choose active, inactive, or expired coupons.');

        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->getJson('/api/store/coupons?status=paused')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'اختر الكوبونات النشطة أو المتوقفة أو المنتهية.');
    }

    public function test_non_store_account_cannot_manage_coupons(): void
    {
        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);

        $this->withToken($this->tokenFor($user))->postJson('/api/store/coupons', $this->couponData())
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.profile.store_type_required'));
    }

    public function test_percentage_discount_is_limited_to_one_hundred(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);

        $this->withHeader('Accept-Language', 'en')->withToken($this->tokenFor($owner))
            ->postJson('/api/store/coupons', [...$this->couponData(), 'discount_value' => 101])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A percentage discount cannot exceed 100.');
    }

    public function test_expired_coupon_cannot_be_reactivated_or_paused(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $coupon = Coupon::query()->create([
            'store_id' => $store->id,
            'code' => 'OLD20',
            'discount_type' => CouponDiscountTypeEnum::Percentage,
            'discount_value' => 20,
            'minimum_order_amount' => 0,
            'max_uses' => 10,
            'max_uses_per_user' => 1,
            'status' => CouponStatusEnum::Active,
            'expires_at' => now()->subDay(),
        ]);

        $this->withToken($this->tokenFor($owner))->patchJson("/api/store/coupons/{$coupon->id}/toggle")
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.coupons.toggle_unavailable'));
    }

    /** @return array<string, mixed> */
    private function couponData(): array
    {
        return [
            'code' => 'saey20',
            'discount_type' => CouponDiscountTypeEnum::Percentage->value,
            'discount_value' => 20,
            'minimum_order_amount' => 500,
            'maximum_discount_amount' => 250,
            'max_uses' => 100,
            'max_uses_per_user' => 1,
            'expires_at' => now()->addYear()->endOfDay()->toDateTimeString(),
            'is_active' => true,
        ];
    }

    private function tokenFor(object $user): string
    {
        return (string) auth('api')->login($user);
    }

    private function createCoupon(
        int $storeId,
        string $code,
        CouponStatusEnum $status,
        \DateTimeInterface $expiresAt,
        int $usesCount = 0,
    ): Coupon {
        return Coupon::query()->create([
            'store_id' => $storeId,
            'code' => $code,
            'discount_type' => CouponDiscountTypeEnum::Percentage,
            'discount_value' => 10,
            'minimum_order_amount' => 0,
            'max_uses' => 10,
            'max_uses_per_user' => 1,
            'uses_count' => $usesCount,
            'status' => $status,
            'expires_at' => $expiresAt,
        ]);
    }
}

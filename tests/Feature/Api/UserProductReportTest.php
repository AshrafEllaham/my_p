<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserReportTypeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Product;
use App\Models\Sai\ReportReason;
use App\Models\Sai\Store;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserProductReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_load_translated_reasons_and_submit_product_report_once(): void
    {
        $reason = ReportReason::query()->create([
            'type' => UserReportTypeEnum::Product,
            'ar' => ['title' => 'منتج مقلّد'],
            'en' => ['title' => 'Counterfeit product'],
        ]);
        ReportReason::query()->create([
            'type' => UserReportTypeEnum::Store,
            'ar' => ['title' => 'متجر وهمي'],
            'en' => ['title' => 'Fake store'],
        ]);
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store, 'status' => UserStatusEnum::Active]);
        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $category = Category::factory()->create();
        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Jacket',
            'description' => 'Jacket',
            'slug' => 'jacket-'.Str::random(6),
            'status' => ProductStatusEnum::Published,
            'price' => 100,
            'stock_quantity' => 1,
        ]);

        $this->actingAs($user, 'api')->withHeader('Accept-Language', 'ar')
            ->getJson('/api/user/report-reasons?type=product')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', UserReportTypeEnum::Product->value)
            ->assertJsonPath('data.0.title', 'منتج مقلّد');
        $this->withHeader('Accept-Language', 'en')->getJson('/api/user/report-reasons?type=product')
            ->assertOk()->assertJsonPath('data.0.title', 'Counterfeit product');

        $url = '/api/user/products/'.$product->id.'/reports';
        $this->postJson($url, ['report_reason_id' => $reason->id])
            ->assertOk()->assertJsonPath('data.type', UserReportTypeEnum::Product->value)
            ->assertJsonPath('data.item_id', $product->id);
        $this->postJson($url, ['report_reason_id' => $reason->id])->assertOk();
        $this->assertDatabaseCount('user_reports', 1);
        $this->assertDatabaseHas('user_reports', [
            'user_id' => $user->id,
            'type' => UserReportTypeEnum::Product->value,
            'item_id' => $product->id,
            'report_reason_id' => $reason->id,
        ]);
    }

    public function test_report_requires_authentication_and_valid_reason_and_visible_product(): void
    {
        $this->getJson('/api/user/report-reasons?type=product')->assertUnauthorized();
        $this->postJson('/api/user/products/1/reports', [])->assertUnauthorized();
        $this->postJson('/api/user/stores/1/reports', [])->assertUnauthorized();

        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $this->actingAs($user, 'api');
        $this->withHeader('Accept-Language', 'ar')->getJson('/api/user/report-reasons')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_reports.type.required'));
        $this->withHeader('Accept-Language', 'en')->getJson('/api/user/report-reasons?type=invalid')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_reports.type.enum'));
        $this->withHeader('Accept-Language', 'ar')->postJson('/api/user/products/1/reports', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_reports.reason.required'));
        $this->withHeader('Accept-Language', 'en')->postJson('/api/user/products/1/reports', ['report_reason_id' => 999])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_reports.reason.exists'));

        $reason = ReportReason::query()->create([
            'type' => UserReportTypeEnum::Product,
            'ar' => ['title' => 'سبب'],
            'en' => ['title' => 'Reason'],
        ]);
        $this->postJson('/api/user/products/999/reports', ['report_reason_id' => $reason->id])->assertNotFound();

        $storeReason = ReportReason::query()->create([
            'type' => UserReportTypeEnum::Store,
            'ar' => ['title' => 'سبب متجر'],
            'en' => ['title' => 'Store reason'],
        ]);
        $this->postJson('/api/user/products/1/reports', ['report_reason_id' => $storeReason->id])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_reports.reason.exists'));
    }

    public function test_user_can_report_active_store_with_store_reason_only(): void
    {
        $storeReason = ReportReason::query()->create([
            'type' => UserReportTypeEnum::Store,
            'ar' => ['title' => 'متجر وهمي'],
            'en' => ['title' => 'Fake store'],
        ]);
        $productReason = ReportReason::query()->create([
            'type' => UserReportTypeEnum::Product,
            'ar' => ['title' => 'منتج مقلّد'],
            'en' => ['title' => 'Counterfeit product'],
        ]);
        $category = Category::factory()->create();
        $owner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $this->actingAs($user, 'api');

        $this->withHeader('Accept-Language', 'ar')->getJson('/api/user/report-reasons?type=store')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', UserReportTypeEnum::Store->value)
            ->assertJsonPath('data.0.title', 'متجر وهمي');

        $url = '/api/user/stores/'.$store->id.'/reports';
        $this->postJson($url, ['report_reason_id' => $productReason->id])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_reports.reason.exists'));
        $this->postJson($url, ['report_reason_id' => $storeReason->id])
            ->assertOk()
            ->assertJsonPath('data.type', UserReportTypeEnum::Store->value)
            ->assertJsonPath('data.item_id', $store->id);
        $this->postJson($url, ['report_reason_id' => $storeReason->id])->assertOk();

        $this->assertDatabaseCount('user_reports', 1);
        $this->assertDatabaseHas('user_reports', [
            'user_id' => $user->id,
            'type' => UserReportTypeEnum::Store->value,
            'item_id' => $store->id,
            'report_reason_id' => $storeReason->id,
        ]);

        $owner->update(['status' => UserStatusEnum::Suspended]);
        $this->postJson($url, ['report_reason_id' => $storeReason->id])->assertNotFound();
    }
}

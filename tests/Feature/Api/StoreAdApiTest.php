<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\AdActionEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\MediaTypeEnum;
use App\Models\Sai\Ad;
use App\Models\Sai\AdDailyMetric;
use App\Models\Sai\AdPackage;
use App\Models\Sai\Category;
use App\Models\Sai\Store;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreAdApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_store_can_create_list_edit_toggle_and_delete_its_ads(): void
    {
        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStore = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $store->id, 'category_id' => $category->id]);
        Store::query()->create(['owner_id' => $otherStore->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'weekly', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $token = $this->tokenFor($store);

        $this->withToken($token)->getJson('/api/store/ad-packages')
            ->assertOk()->assertJsonPath('data.0.id', $package->id);

        $created = $this->withToken($token)->post('/api/store/ads', [
            'ad_package_id' => $package->id,
            'title' => 'Summer offer',
            'placement' => AdPlacementEnum::Home->value,
            'action' => AdActionEnum::ViewProduct->value,
            'media_type' => MediaTypeEnum::Image->value,
            'media' => UploadedFile::fake()->image('offer.jpg'),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('message', __('messages.ads.created'))
            ->assertJsonPath('data.status', AdStatusEnum::Draft->value)
            ->assertJsonPath('data.cost', '350.00');

        $adId = $created->json('data.id');
        Ad::query()->whereKey($adId)->update(['status' => AdStatusEnum::Active->value]);

        $this->withToken($token)->patchJson("/api/store/ads/{$adId}", ['title' => 'Updated offer'])
            ->assertOk()->assertJsonPath('data.title', 'Updated offer');

        $this->withToken($token)->patchJson("/api/store/ads/{$adId}/toggle")
            ->assertOk()->assertJsonPath('data.status', AdStatusEnum::Paused->value);

        $this->withToken($token)->getJson('/api/store/ads?status=paused')
            ->assertOk()->assertJsonPath('data.0.id', $adId);

        $this->withToken($token)->deleteJson("/api/store/ads/{$adId}")
            ->assertOk()->assertJsonPath('message', __('messages.ads.deleted'));
        $this->assertSoftDeleted('ads', ['id' => $adId]);

        $this->withToken($this->tokenFor($otherStore))->getJson('/api/store/ads')
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_ad_validation_errors_are_localized_and_store_access_is_required(): void
    {
        $this->postJson('/api/store/ads')->assertUnauthorized();

        $store = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        Store::query()->create(['owner_id' => $store->id, 'category_id' => $category->id]);
        $token = $this->tokenFor($store);

        $this->withHeader('Accept-Language', 'en')->withToken($token)
            ->postJson('/api/store/ads', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Choose an active ad package.');

        $this->withHeader('Accept-Language', 'ar')->withToken($token)
            ->postJson('/api/store/ads', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'اختر باقة إعلان نشطة.');

        $user = UserFactory::new()->create(['account_type' => AccountTypeEnum::User]);
        $this->withToken($this->tokenFor($user))->getJson('/api/store/ads')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.profile.store_type_required'));
    }

    public function test_store_can_get_ad_details_and_only_seven_days_of_metrics(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherOwner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        Store::query()->create(['owner_id' => $otherOwner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'weekly-details', 'duration_days' => 7, 'price' => 350, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $ad = Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $store->id,
            'ad_package_id' => $package->id,
            'title' => 'Details campaign',
            'placement' => AdPlacementEnum::Home,
            'action' => AdActionEnum::ViewStore,
            'media_type' => MediaTypeEnum::Image,
            'media_path' => 'ads/details.jpg',
            'status' => AdStatusEnum::Active,
            'cost' => 350,
            'currency' => 'EGP',
        ]);
        AdDailyMetric::query()->create([
            'ad_id' => $ad->id,
            'metric_date' => now()->subDays(2)->toDateString(),
            'impressions' => 120,
            'clicks' => 15,
            'chats_started' => 4,
            'orders_attributed' => 2,
            'revenue_attributed' => 500,
        ]);
        AdDailyMetric::query()->create([
            'ad_id' => $ad->id,
            'metric_date' => now()->subDays(8)->toDateString(),
            'impressions' => 900,
            'clicks' => 90,
            'chats_started' => 20,
            'orders_attributed' => 10,
            'revenue_attributed' => 2400,
        ]);

        $this->withToken($this->tokenFor($owner))->getJson("/api/store/ads/{$ad->id}")
            ->assertOk()
            ->assertJsonPath('message', __('messages.ads.retrieved'))
            ->assertJsonPath('data.title', 'Details campaign')
            ->assertJsonPath('data.package.duration_days', 7)
            ->assertJsonPath('data.metrics.impressions', 120)
            ->assertJsonPath('data.metrics.clicks', 15)
            ->assertJsonCount(1, 'data.daily_metrics');

        $this->withToken($this->tokenFor($otherOwner))->getJson("/api/store/ads/{$ad->id}")
            ->assertNotFound();
    }

    public function test_store_cannot_edit_or_delete_another_stores_ad(): void
    {
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $otherStoreOwner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $ownerStore = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        Store::query()->create(['owner_id' => $otherStoreOwner->id, 'category_id' => $category->id]);
        $package = AdPackage::query()->create([
            'code' => 'monthly', 'duration_days' => 30, 'price' => 900, 'currency' => 'EGP', 'is_active' => true,
        ]);
        $ad = Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $ownerStore->id,
            'ad_package_id' => $package->id,
            'title' => 'Private campaign',
            'placement' => AdPlacementEnum::Home,
            'action' => AdActionEnum::ViewStore,
            'media_type' => MediaTypeEnum::Image,
            'media_path' => 'ads/private.jpg',
            'status' => AdStatusEnum::Draft,
            'cost' => 900,
            'currency' => 'EGP',
        ]);

        $token = $this->tokenFor($otherStoreOwner);
        $this->withToken($token)->patchJson("/api/store/ads/{$ad->id}", ['title' => 'Changed'])
            ->assertNotFound();
        $this->withToken($token)->deleteJson("/api/store/ads/{$ad->id}")
            ->assertNotFound();
        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'title' => 'Private campaign', 'deleted_at' => null]);
    }

    public function test_ad_preview_uses_store_ad_endpoints_for_campaign_management(): void
    {
        $adsScreen = file_get_contents(base_path('Figma/screens/19-distributor-ads.html'));
        $editorScreen = file_get_contents(base_path('Figma/screens/19-add-ad.html'));
        $detailsScreen = file_get_contents(base_path('Figma/screens/distributor-ad-details.html'));

        $this->assertIsString($adsScreen);
        $this->assertIsString($editorScreen);
        $this->assertIsString($detailsScreen);
        $this->assertStringContainsString('/ads?${query}', $adsScreen);
        $this->assertStringContainsString('/toggle', $adsScreen);
        $this->assertStringContainsString("'/ad-packages'", $editorScreen);
        $this->assertStringContainsString("'/ads'", $editorScreen);
        $this->assertStringContainsString('`/ads/${editId}`', $editorScreen);
        $this->assertStringContainsString('/ads/${encodeURIComponent(selectedAdId)}', $detailsScreen);
        $this->assertStringContainsString('ad.daily_metrics', $detailsScreen);
    }

    private function tokenFor(object $user): string
    {
        return (string) auth('api')->login($user);
    }
}

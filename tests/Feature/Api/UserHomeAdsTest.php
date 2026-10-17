<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\AdActionEnum;
use App\Enums\AdInteractionEventTypeEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\MediaTypeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Ad;
use App\Models\Sai\AdDailyMetric;
use App\Models\Sai\AdInteractionEvent;
use App\Models\Sai\AdPackage;
use App\Models\Sai\Category;
use App\Models\Sai\Store;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserHomeAdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_only_active_ads_for_the_requested_placement_with_store_details(): void
    {
        [$store, $user, $package] = $this->createStoreAndUser();
        $olderHomeAd = $this->createAd($store, $package, 'إعلان أقدم', AdPlacementEnum::Home, AdStatusEnum::Active, now()->subDay(), now()->addDay());
        $newestHomeAd = $this->createAd($store, $package, 'إعلان الرئيسية', AdPlacementEnum::Home, AdStatusEnum::Active);
        $this->createAd($store, $package, 'إعلان قسم', AdPlacementEnum::Category, AdStatusEnum::Active);
        $this->createAd($store, $package, 'إعلان يبدأ لاحقًا', AdPlacementEnum::Home, AdStatusEnum::Active, now()->addDay(), now()->addDays(2));
        $this->createAd($store, $package, 'إعلان منتهي', AdPlacementEnum::Home, AdStatusEnum::Active, now()->subDays(2), now()->subSecond());
        $this->createAd($store, $package, 'إعلان متوقف', AdPlacementEnum::Home, AdStatusEnum::Paused);

        [$inactiveStore, , $inactivePackage] = $this->createStoreAndUser(UserStatusEnum::Suspended);
        $this->createAd($inactiveStore, $inactivePackage, 'متجر غير نشط', AdPlacementEnum::Home, AdStatusEnum::Active);

        $this->getJson('/api/user/ads?placement=home')->assertUnauthorized();

        $this->actingAs($user, 'api')
            ->getJson('/api/user/ads?placement=home&limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('message', __('messages.user_ads.listed'))
            ->assertJsonPath('total', 2)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('data.0.id', $newestHomeAd->id)
            ->assertJsonPath('data.0.title', 'إعلان الرئيسية')
            ->assertJsonPath('data.0.placement', AdPlacementEnum::Home->value)
            ->assertJsonPath('data.0.action', AdActionEnum::ViewStore->value)
            ->assertJsonPath('data.0.media_type', MediaTypeEnum::Image->value)
            ->assertJsonPath('data.0.store.id', $store->id)
            ->assertJsonPath('data.0.store.name', $store->owner->name)
            ->assertJsonPath('data.0.target.store_id', $store->id)
            ->assertJsonMissingPath('data.0.cost')
            ->assertJsonMissingPath('data.0.status');

        $this->getJson('/api/user/ads?placement=home&limit_per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('data.0.id', $olderHomeAd->id);

        $this->getJson('/api/user/ads?placement=category')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'إعلان قسم');
    }

    public function test_impressions_and_clicks_update_daily_metrics_once_per_event_id(): void
    {
        [$store, $user, $package] = $this->createStoreAndUser();
        $ad = $this->createAd($store, $package, 'إعلان قابل للتفاعل', AdPlacementEnum::Home, AdStatusEnum::Active);
        $this->actingAs($user, 'api');

        $impressionId = (string) Str::uuid();
        $this->postJson("/api/user/ads/{$ad->id}/impressions", ['event_id' => $impressionId])
            ->assertOk()
            ->assertJsonPath('data.event_id', $impressionId)
            ->assertJsonPath('data.type', AdInteractionEventTypeEnum::Impression->value)
            ->assertJsonPath('data.recorded', true);

        $this->postJson("/api/user/ads/{$ad->id}/impressions", ['event_id' => $impressionId])
            ->assertOk()
            ->assertJsonPath('data.recorded', false);

        $clickId = (string) Str::uuid();
        $this->postJson("/api/user/ads/{$ad->id}/clicks", ['event_id' => $clickId])
            ->assertOk()
            ->assertJsonPath('data.type', AdInteractionEventTypeEnum::Click->value)
            ->assertJsonPath('data.recorded', true);

        $this->assertSame(2, AdInteractionEvent::query()->count());
        $this->assertDatabaseHas('ad_daily_metrics', [
            'ad_id' => $ad->id,
            'metric_date' => today()->toDateString(),
            'impressions' => 1,
            'clicks' => 1,
        ]);

        $this->postJson("/api/user/ads/{$ad->id}/clicks", ['event_id' => $impressionId])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.ads.event_id_conflict'));

        $dailyMetric = AdDailyMetric::query()->where('ad_id', $ad->id)->firstOrFail();
        $this->assertSame(1, $dailyMetric->impressions);
        $this->assertSame(1, $dailyMetric->clicks);

        $pausedAd = $this->createAd($store, $package, 'إعلان متوقف', AdPlacementEnum::Home, AdStatusEnum::Paused);
        $this->postJson("/api/user/ads/{$pausedAd->id}/impressions", ['event_id' => (string) Str::uuid()])
            ->assertNotFound();
        $this->assertSame(2, AdInteractionEvent::query()->count());
    }

    public function test_ad_interaction_requires_authentication_and_translates_validation_errors(): void
    {
        $this->postJson('/api/user/ads/1/impressions', [])->assertUnauthorized();
        $this->getJson('/api/user/ads?placement=home')->assertUnauthorized();

        [, $user] = $this->createStoreAndUser();
        $this->actingAs($user, 'api');

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/ads?placement=featured')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_ads.placement.enum'));

        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/user/ads?placement=storefront')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_ads.placement.enum'));

        $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/user/ads/1/impressions', ['event_id' => 'bad-id'])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_ads.event_id.uuid'));

        $this->withHeader('Accept-Language', 'en')
            ->postJson('/api/user/ads/1/clicks', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_ads.event_id.required'));
    }

    public function test_home_preview_loads_sponsored_ads_and_records_impressions_and_clicks(): void
    {
        $preview = file_get_contents(base_path('Figma/screens/08-user-home.html'));
        $script = file_get_contents(base_path('Figma/screens/user-home-sponsored-ads.js'));

        $this->assertIsString($preview);
        $this->assertIsString($script);
        $this->assertStringContainsString('id="homeSponsoredAds"', $preview);
        $this->assertStringContainsString('user-home-sponsored-ads.js', $preview);
        $this->assertStringContainsString('/api/user/ads?placement=home', $script);
        $this->assertStringContainsString("interaction === 'impression' ? 'impressions' : 'clicks'", $script);
        $this->assertStringContainsString('IntersectionObserver', $script);
    }

    private function createStoreAndUser(UserStatusEnum $storeStatus = UserStatusEnum::Active): array
    {
        $owner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => $storeStatus,
        ]);
        $user = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
        $category = Category::factory()->create();
        $store = Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
        ]);
        $package = AdPackage::query()->create([
            'code' => 'weekly-'.Str::lower(Str::random(8)),
            'duration_days' => 7,
            'price' => 100,
            'currency' => 'EGP',
            'is_active' => true,
        ]);

        return [$store, $user, $package];
    }

    private function createAd(
        Store $store,
        AdPackage $package,
        string $title,
        AdPlacementEnum $placement,
        AdStatusEnum $status,
        mixed $startsAt = null,
        mixed $endsAt = null,
    ): Ad {
        return Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $store->id,
            'ad_package_id' => $package->id,
            'title' => $title,
            'placement' => $placement,
            'action' => AdActionEnum::ViewStore,
            'media_type' => MediaTypeEnum::Image,
            'media_path' => 'ads/home.jpg',
            'status' => $status,
            'cost' => 100,
            'currency' => 'EGP',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }
}

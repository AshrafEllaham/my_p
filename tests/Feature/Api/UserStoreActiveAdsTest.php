<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\AdActionEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\MediaTypeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Ad;
use App\Models\Sai\AdPackage;
use App\Models\Sai\Category;
use App\Models\Sai\Store;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserStoreActiveAdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_currently_visible_ads_for_a_store_with_pagination(): void
    {
        [$store, $customer, $package] = $this->createStoreAndCustomer();
        $olderActive = $this->createAd($store->id, $package->id, 'Older active', AdStatusEnum::Active, now()->subDay(), now()->addDays(2));
        $dueScheduled = $this->createAd($store->id, $package->id, 'Scheduled campaign in progress', AdStatusEnum::Scheduled, now()->subHour(), now()->addDays(3));
        $newestActive = $this->createAd($store->id, $package->id, 'Newest active', AdStatusEnum::Active);
        $this->createAd($store->id, $package->id, 'Starts later', AdStatusEnum::Active, now()->addDay(), now()->addDays(2));
        $this->createAd($store->id, $package->id, 'Already ended', AdStatusEnum::Active, now()->subDays(2), now()->subSecond());
        $this->createAd($store->id, $package->id, 'Paused', AdStatusEnum::Paused);
        $otherStore = $this->createAnotherStore();
        $this->createAd($otherStore->id, $package->id, 'Other store', AdStatusEnum::Active);
        $this->actingAs($customer, 'api');

        $url = "/api/user/stores/{$store->id}/ads?limit_per_page=1";
        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('message', __('messages.user_stores.ads_listed'))
            ->assertJsonPath('total', 3)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('data.0.id', $newestActive->id)
            ->assertJsonPath('data.0.title', 'Newest active')
            ->assertJsonPath('data.0.placement', AdPlacementEnum::Storefront->value)
            ->assertJsonPath('data.0.action', AdActionEnum::ViewStore->value)
            ->assertJsonPath('data.0.media_type', MediaTypeEnum::Image->value)
            ->assertJsonMissingPath('data.0.cost')
            ->assertJsonMissingPath('data.0.status');

        $this->getJson($url.'&page=2')
            ->assertOk()
            ->assertJsonPath('data.0.id', $dueScheduled->id);

        $this->getJson($url.'&page=3')
            ->assertOk()
            ->assertJsonPath('data.0.id', $olderActive->id);
    }

    public function test_store_ad_list_requires_auth_validates_pagination_and_hides_unavailable_stores(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $url = "/api/user/stores/{$store->id}/ads";

        $this->getJson($url)->assertUnauthorized();
        $this->actingAs($customer, 'api');
        $this->withHeader('Accept-Language', 'en')
            ->getJson($url.'?page=0')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.page.min'));
        $this->withHeader('Accept-Language', 'ar')
            ->getJson($url.'?limit_per_page=101')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.limit_per_page.max'));
        $this->getJson('/api/user/stores/0/ads')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_store_details.store.min'));
        $this->getJson('/api/user/stores/999999/ads')->assertNotFound();

        $store->owner()->update(['status' => UserStatusEnum::Suspended]);
        $this->getJson($url)->assertNotFound();
    }

    public function test_store_details_preview_loads_ads_from_the_paginated_store_endpoint(): void
    {
        $preview = file_get_contents(base_path('Figma/screens/19-store-details.html'));
        $script = file_get_contents(base_path('Figma/screens/user-store-details.js'));
        $sponsoredAdsScript = file_get_contents(base_path('Figma/screens/sponsored-ads.js'));

        $this->assertIsString($preview);
        $this->assertIsString($script);
        $this->assertIsString($sponsoredAdsScript);
        $this->assertStringContainsString('id="storeAdsSection"', $preview);
        $this->assertStringContainsString('id="loadMoreStoreAds"', $preview);
        $this->assertStringContainsString('/api/user/stores/${encodeURIComponent(storeId)}/ads', $script);
        $this->assertStringContainsString('loadStoreActiveAds()', $script);
        $this->assertStringContainsString('page: String(requestedPage)', $script);
        $this->assertStringContainsString('لا توجد إعلانات نشطة لهذا المتجر حاليًا.', $script);
        $this->assertStringContainsString("retry.textContent = 'إعادة المحاولة'", $script);
        $this->assertStringContainsString("'.youtube-ad:not(.store-active-ad)'", $sponsoredAdsScript);
    }

    private function createStoreAndCustomer(): array
    {
        $owner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $customer = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::User,
            'status' => UserStatusEnum::Active,
        ]);
        $category = Category::factory()->create();
        $store = Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
        ]);
        $package = AdPackage::query()->create([
            'code' => 'weekly-'.Str::lower(Str::random(6)),
            'duration_days' => 7,
            'price' => 100,
            'currency' => 'EGP',
            'is_active' => true,
        ]);

        return [$store, $customer, $package];
    }

    private function createAnotherStore(): Store
    {
        $owner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $category = Category::factory()->create();

        return Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
        ]);
    }

    private function createAd(
        int $storeId,
        int $packageId,
        string $title,
        AdStatusEnum $status,
        mixed $startsAt = null,
        mixed $endsAt = null,
    ): Ad {
        return Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $storeId,
            'ad_package_id' => $packageId,
            'title' => $title,
            'placement' => AdPlacementEnum::Storefront,
            'action' => AdActionEnum::ViewStore,
            'media_type' => MediaTypeEnum::Image,
            'media_path' => 'ads/storefront.jpg',
            'status' => $status,
            'cost' => 100,
            'currency' => 'EGP',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }
}

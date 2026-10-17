<?php

namespace Tests\Feature\Admin;

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
use App\Services\Admin\AdReviewService;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_home_ad_is_queued_after_the_current_campaign(): void
    {
        $owner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => UserStatusEnum::Active,
        ]);
        $category = Category::factory()->create();
        $store = Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
        ]);
        $package = AdPackage::query()->create([
            'code' => 'weekly-home-'.Str::lower(Str::random(8)),
            'duration_days' => 7,
            'price' => 100,
            'currency' => 'EGP',
            'is_active' => true,
        ]);
        $currentEndsAt = now()->addDay()->startOfSecond();

        $this->createAd($store, $package, 'الحملة الحالية', AdStatusEnum::Active, now()->subDay(), $currentEndsAt);
        $pendingAd = $this->createAd($store, $package, 'الحملة التالية', AdStatusEnum::PendingReview);

        $approvedAd = app(AdReviewService::class)->approve($pendingAd->id);

        $this->assertSame(AdStatusEnum::Scheduled, $approvedAd->status);
        $this->assertEquals($currentEndsAt, $approvedAd->starts_at);
        $this->assertEquals($currentEndsAt->copy()->addDays(7), $approvedAd->ends_at);
    }

    private function createAd(
        Store $store,
        AdPackage $package,
        string $title,
        AdStatusEnum $status,
        mixed $startsAt = null,
        mixed $endsAt = null,
    ): Ad {
        return Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $store->id,
            'ad_package_id' => $package->id,
            'title' => $title,
            'placement' => AdPlacementEnum::Home,
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

<?php

namespace Tests\Feature\Api;

use App\Enums\AccountTypeEnum;
use App\Enums\CompetitionStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Category;
use App\Models\Sai\Competition;
use App\Models\Sai\Store;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserCompetitionCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_paginated_competitions_by_availability_and_ended_status(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $endingSoon = $this->createCompetition($store->id, 'Ends soon', CompetitionStatusEnum::Active, now()->addDay());
        $this->createCompetition($store->id, 'Ends later', CompetitionStatusEnum::Active, now()->addDays(3));
        $this->createCompetition($store->id, 'Expired but not drawn', CompetitionStatusEnum::Active, now()->subHour());
        $ended = $this->createCompetition($store->id, 'Ended', CompetitionStatusEnum::Ended, now()->subDays(2));
        $drawn = $this->createCompetition($store->id, 'Drawn', CompetitionStatusEnum::Drawn, now()->subDays(3));
        $this->createCompetition($store->id, 'Cancelled', CompetitionStatusEnum::Cancelled, now()->subDay());
        $this->actingAs($customer, 'api');

        $this->getJson('/api/user/competitions?status=available_now&limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('message', __('messages.user_competitions.listed'))
            ->assertJsonPath('total', 2)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('data.0.id', $endingSoon->id)
            ->assertJsonPath('data.0.status', 'available_now')
            ->assertJsonPath('data.0.store.name', $store->owner->name)
            ->assertJsonPath('data.0.store.image', Storage::disk('public')->url('stores/cover.jpg'))
            ->assertJsonMissingPath('data.0.winner_user_id');

        $this->getJson('/api/user/competitions?status=available_now&limit_per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('data.0.title', 'Ends later');

        $this->getJson('/api/user/competitions?status=ended&limit_per_page=10')
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('data.0.id', $this->competitionIdByTitle($store->id, 'Expired but not drawn'))
            ->assertJsonPath('data.0.status', 'ended')
            ->assertJsonPath('data.1.id', $ended->id)
            ->assertJsonPath('data.2.id', $drawn->id);
    }

    public function test_user_competition_filter_is_optional_and_only_visible_store_competitions_are_listed(): void
    {
        [$store, $customer] = $this->createStoreAndCustomer();
        $this->createCompetition($store->id, 'Visible', CompetitionStatusEnum::Active, now()->addDay());
        $hiddenStore = $this->createAnotherStore(UserStatusEnum::Suspended);
        $this->createCompetition($hiddenStore->id, 'Hidden store', CompetitionStatusEnum::Active, now()->addDay());
        $this->actingAs($customer, 'api');

        $this->getJson('/api/user/competitions')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.title', 'Visible');
    }

    public function test_user_competition_endpoint_requires_auth_and_localizes_filter_validation(): void
    {
        $this->getJson('/api/user/competitions')->assertUnauthorized();
        [, $customer] = $this->createStoreAndCustomer();
        $this->actingAs($customer, 'api');

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/competitions?status=all')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.user_competitions.status.enum'));

        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/user/competitions?page=0')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.page.min'));

        $this->getJson('/api/user/competitions?limit_per_page=101')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.limit_per_page.max'));
    }

    public function test_home_and_competitions_preview_use_the_paginated_user_competitions_endpoint(): void
    {
        $home = file_get_contents(base_path('Figma/screens/08-user-home.html'));
        $listing = file_get_contents(base_path('Figma/screens/27-competitions.html'));
        $script = file_get_contents(base_path('Figma/screens/user-competitions.js'));

        $this->assertIsString($home);
        $this->assertIsString($listing);
        $this->assertIsString($script);
        $this->assertStringContainsString('src="user-competitions.js"', $home);
        $this->assertStringContainsString('src="user-competitions.js"', $listing);
        $this->assertStringContainsString('data-filter="available_now"', $listing);
        $this->assertStringContainsString('data-filter="ended"', $listing);
        $this->assertStringContainsString('/api/user/competitions?', $script);
        $this->assertStringContainsString("requestPage('available_now', 1, 2)", $script);
        $this->assertStringContainsString('loadMoreCompetitions', $listing);
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
        $category = Category::factory()->create(['is_active' => true]);
        $store = Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'cover_image' => 'stores/cover.jpg',
        ]);

        return [$store, $customer];
    }

    private function createAnotherStore(UserStatusEnum $ownerStatus): Store
    {
        $owner = UserFactory::new()->create([
            'account_type' => AccountTypeEnum::Store,
            'status' => $ownerStatus,
        ]);
        $category = Category::factory()->create(['is_active' => true]);

        return Store::query()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
        ]);
    }

    private function createCompetition(
        int $storeId,
        string $title,
        CompetitionStatusEnum $status,
        mixed $endsAt,
    ): Competition {
        return Competition::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $storeId,
            'content_locale' => 'ar',
            'title' => $title,
            'question' => 'ما هي إجابتك؟',
            'prize' => 'قسيمة تسوق',
            'status' => $status,
            'ends_at' => $endsAt,
        ]);
    }

    private function competitionIdByTitle(int $storeId, string $title): int
    {
        return (int) Competition::query()->where('store_id', $storeId)->where('title', $title)->value('id');
    }
}

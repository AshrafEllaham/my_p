<?php

namespace Tests\Feature\Api;

use App\Models\Sai\Category;
use App\Models\Sai\City;
use App\Models\Sai\Country;
use App\Models\Sai\Governorate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogLookupPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept-Language', 'ar');
    }

    public function test_main_categories_are_paginated_and_exclude_subcategories(): void
    {
        $first = Category::factory()->create(['sort_order' => 1, 'ar' => ['name' => 'القسم الأول']]);
        Category::factory()->create(['sort_order' => 2, 'ar' => ['name' => 'القسم الثاني']]);
        Category::factory()->subcategory($first)->create(['sort_order' => 3]);

        $this->getJson('/api/main-categories?pagination=on&limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'القسم الأول');

        $this->getJson('/api/main-categories?pagination=on&limit_per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('data.0.name', 'القسم الثاني');
    }

    public function test_subcategories_are_filtered_by_main_category_and_paginated(): void
    {
        $main = Category::factory()->create();
        $otherMain = Category::factory()->create();
        Category::factory()->subcategory($main)->create(['sort_order' => 1, 'ar' => ['name' => 'فرعي أول']]);
        Category::factory()->subcategory($main)->create(['sort_order' => 2, 'ar' => ['name' => 'فرعي ثان']]);
        Category::factory()->subcategory($otherMain)->create();

        $this->getJson("/api/sub-categories?main_category_id={$main->id}&pagination=on&limit_per_page=1")
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.parent_id', $main->id);
    }

    public function test_countries_are_paginated(): void
    {
        Country::factory()->create(['code' => 'EG', 'ar' => ['name' => 'مصر']]);
        Country::factory()->create(['code' => 'SA', 'ar' => ['name' => 'السعودية']]);
        Country::factory()->create(['code' => 'AE', 'is_active' => false]);

        $this->getJson('/api/countries?pagination=on&limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('last_page', 2)
            ->assertJsonCount(1, 'data');
    }

    public function test_governorates_are_filtered_by_country_and_paginated(): void
    {
        $country = Country::factory()->create(['code' => 'EG']);
        $otherCountry = Country::factory()->create(['code' => 'SA']);
        Governorate::factory()->create(['country_id' => $country->id]);
        Governorate::factory()->create(['country_id' => $country->id]);
        Governorate::factory()->create(['country_id' => $otherCountry->id]);

        $this->getJson("/api/governorates?country_id={$country->id}&pagination=on&limit_per_page=1")
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.country_id', $country->id);
    }

    public function test_cities_are_filtered_by_governorate_and_paginated(): void
    {
        $governorate = Governorate::factory()->create();
        $otherGovernorate = Governorate::factory()->create();
        City::factory()->create(['governorate_id' => $governorate->id]);
        City::factory()->create(['governorate_id' => $governorate->id]);
        City::factory()->create(['governorate_id' => $otherGovernorate->id]);

        $this->getJson("/api/cities?governorate_id={$governorate->id}&pagination=on&limit_per_page=1")
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.governorate_id', $governorate->id);
    }

    public function test_pagination_limit_validation_returns_a_localized_api_error(): void
    {
        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/countries?pagination=on&limit_per_page=101')
            ->assertUnprocessable()
            ->assertJsonPath('code', 422)
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.limit_per_page.max'));
    }
}

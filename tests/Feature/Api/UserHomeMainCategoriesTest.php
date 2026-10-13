<?php

namespace Tests\Feature\Api;

use App\Models\Sai\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserHomeMainCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_only_active_root_categories_in_pages(): void
    {
        $first = Category::factory()->create(['sort_order' => 1, 'ar' => ['name' => 'الأول'], 'en' => ['name' => 'First']]);
        Category::factory()->create(['sort_order' => 2, 'ar' => ['name' => 'الثاني'], 'en' => ['name' => 'Second']]);
        Category::factory()->subcategory($first)->create();
        Category::factory()->create(['is_active' => false]);

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/main-categories?pagination=on&limit_per_page=1')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('data.0.name', 'First')
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/user/main-categories?pagination=on&limit_per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('data.0.name', 'Second');

        $this->getJson('/api/user/main-categories?pagination=on&search=Sec')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.name', 'Second');
    }

    public function test_home_categories_are_available_without_authentication(): void
    {
        Category::factory()->create(['ar' => ['name' => 'قسم']]);

        $this->getJson('/api/user/main-categories?pagination=on')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_home_subcategories_are_filtered_by_the_required_main_category(): void
    {
        $main = Category::factory()->create();
        $otherMain = Category::factory()->create();
        $firstSubcategory = Category::factory()->subcategory($main)->create(['sort_order' => 1]);
        Category::factory()->subcategory($main)->create(['sort_order' => 2]);
        Category::factory()->subcategory($otherMain)->create();
        Category::factory()->subcategory($main)->create(['is_active' => false]);

        $this->getJson("/api/user/sub-categories?main_category_id={$main->id}&pagination=on&limit_per_page=1")
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('data.0.id', $firstSubcategory->id);

        $this->getJson('/api/user/sub-categories')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.main_category_id.required'));

        $this->getJson('/api/user/sub-categories?main_category_id=invalid')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.main_category_id.integer'));

        $this->getJson("/api/user/sub-categories?main_category_id={$firstSubcategory->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.main_category_id.exists'));
    }

    public function test_home_category_pagination_errors_are_translated(): void
    {
        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/user/main-categories?limit_per_page=101')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.limit_per_page.max'));

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/user/main-categories?page=0')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.page.min'));

        $this->getJson('/api/user/main-categories?search='.str_repeat('a', 121))
            ->assertUnprocessable()
            ->assertJsonPath('message', __('messages.validation.catalog_lookup.search.max'));
    }

    public function test_home_eager_loads_category_translations_once_per_page(): void
    {
        Category::factory()->count(12)->create();
        $translationQueries = 0;
        DB::listen(function ($query) use (&$translationQueries): void {
            if (str_contains($query->sql, 'category_translations') && str_starts_with(strtolower(trim($query->sql)), 'select')) {
                $translationQueries++;
            }
        });

        $this->getJson('/api/user/main-categories?pagination=on&limit_per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data');

        $this->assertSame(1, $translationQueries);
    }
}

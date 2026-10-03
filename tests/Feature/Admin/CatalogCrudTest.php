<?php

namespace Tests\Feature\Admin;

use App\Models\Admin\Admin;
use App\Models\Twenty\Category;
use App\Models\Twenty\City;
use App\Models\Twenty\Country;
use App\Models\Twenty\Governorate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CatalogCrudTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);
        $this->admin = Admin::factory()->create();
    }

    /** @return array<string, array{string}> */
    public static function catalogRoutes(): array
    {
        return [
            'countries' => ['admin.countries.index'],
            'governorates' => ['admin.governorates.index'],
            'cities' => ['admin.cities.index'],
            'main categories' => ['admin.main-categories.index'],
            'subcategories' => ['admin.sub-categories.index'],
        ];
    }

    #[DataProvider('catalogRoutes')]
    public function test_catalog_pages_require_admin_authentication(string $route): void
    {
        $this->get(route($route))->assertRedirect(route('admin.login'));
    }

    #[DataProvider('catalogRoutes')]
    public function test_authenticated_admin_can_open_catalog_pages(string $route): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route($route))
            ->assertOk()
            ->assertViewIs('admin.catalog.index')
            ->assertSee('data-admin-sidebar', false)
            ->assertSee('class="admin-sidebar__brand"', false)
            ->assertSee('data-sidebar-toggle', false)
            ->assertSee('data-catalog-navigation', false)
            ->assertSee('class="admin-nav-group__menu"', false)
            ->assertSee('class="admin-nav__subitem-icon"', false)
            ->assertSee('data-delete-modal', false)
            ->assertSee('data-delete-confirm', false)
            ->assertSee('role="alertdialog"', false)
            ->assertSee('data-catalog-notice hidden', false)
            ->assertSee('data-catalog-error hidden', false);

        $this->assertMatchesRegularExpression(
            '/<details[^>]*data-catalog-navigation[^>]*\sopen(?:\s|>)/',
            $response->getContent()
        );
    }

    public function test_admin_can_create_update_and_delete_a_country_with_translations(): void
    {
        $createResponse = $this->actingAs($this->admin, 'admin')->postJson(route('admin.countries.store'), [
            'code' => 'sa',
            'phone_code' => '+966',
            'flag' => '🇸🇦',
            'is_active' => true,
            'ar' => ['name' => 'السعودية'],
            'en' => ['name' => 'Saudi Arabia'],
        ]);

        $createResponse->assertOk()->assertJsonPath('status', true);
        $country = Country::query()->where('code', 'SA')->firstOrFail();
        $this->assertDatabaseHas('country_translations', ['country_id' => $country->id, 'locale' => 'ar', 'name' => 'السعودية']);
        $this->assertDatabaseHas('country_translations', ['country_id' => $country->id, 'locale' => 'en', 'name' => 'Saudi Arabia']);

        $this->actingAs($this->admin, 'admin')->putJson(route('admin.countries.update', $country), [
            'code' => 'SA',
            'phone_code' => '+966',
            'flag' => '🇸🇦',
            'is_active' => false,
            'ar' => ['name' => 'المملكة العربية السعودية'],
            'en' => ['name' => 'Kingdom of Saudi Arabia'],
        ])->assertOk()->assertJsonPath('status', true);

        $this->assertDatabaseHas('countries', ['id' => $country->id, 'is_active' => false]);
        $this->assertDatabaseHas('country_translations', ['country_id' => $country->id, 'locale' => 'en', 'name' => 'Kingdom of Saudi Arabia']);

        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.countries.destroy', $country))
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
        $this->assertDatabaseMissing('country_translations', ['country_id' => $country->id]);
    }

    public function test_country_validation_rejects_invalid_and_duplicate_values(): void
    {
        $country = Country::factory()->create(['code' => 'EG']);

        $this->actingAs($this->admin, 'admin')->postJson(route('admin.countries.store'), [
            'code' => 'EG',
            'phone_code' => 'invalid',
            'is_active' => true,
            'ar' => ['name' => ''],
            'en' => ['name' => 'Egypt'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'phone_code', 'ar.name']);

        $this->assertDatabaseCount('countries', 1);
        $this->assertDatabaseHas('countries', ['id' => $country->id]);
    }

    public function test_admin_can_manage_governorates_and_cities_and_parent_deletion_is_guarded(): void
    {
        $country = Country::factory()->create();

        $this->actingAs($this->admin, 'admin')->postJson(route('admin.governorates.store'), [
            'country_id' => $country->id,
            'is_active' => true,
            'ar' => ['name' => 'القاهرة'],
            'en' => ['name' => 'Cairo'],
        ])->assertOk();

        $governorate = Governorate::query()->where('country_id', $country->id)->firstOrFail();

        $this->actingAs($this->admin, 'admin')->postJson(route('admin.cities.store'), [
            'governorate_id' => $governorate->id,
            'is_active' => true,
            'ar' => ['name' => 'مدينة نصر'],
            'en' => ['name' => 'Nasr City'],
        ])->assertOk();

        $city = City::query()->where('governorate_id', $governorate->id)->firstOrFail();
        $this->assertDatabaseHas('city_translations', ['city_id' => $city->id, 'locale' => 'ar', 'name' => 'مدينة نصر']);

        $this->actingAs($this->admin, 'admin')->putJson(route('admin.governorates.update', $governorate), [
            'country_id' => $country->id,
            'is_active' => false,
            'ar' => ['name' => 'محافظة القاهرة'],
            'en' => ['name' => 'Cairo Governorate'],
        ])->assertOk();

        $this->actingAs($this->admin, 'admin')->putJson(route('admin.cities.update', $city), [
            'governorate_id' => $governorate->id,
            'is_active' => false,
            'ar' => ['name' => 'حي مدينة نصر'],
            'en' => ['name' => 'Nasr City District'],
        ])->assertOk();

        $this->assertDatabaseHas('governorates', ['id' => $governorate->id, 'is_active' => false]);
        $this->assertDatabaseHas('governorate_translations', ['governorate_id' => $governorate->id, 'locale' => 'en', 'name' => 'Cairo Governorate']);
        $this->assertDatabaseHas('cities', ['id' => $city->id, 'is_active' => false]);
        $this->assertDatabaseHas('city_translations', ['city_id' => $city->id, 'locale' => 'en', 'name' => 'Nasr City District']);

        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.countries.destroy', $country))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('delete');

        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.governorates.destroy', $governorate))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('delete');

        $this->actingAs($this->admin, 'admin')->deleteJson(route('admin.cities.destroy', $city))->assertOk();
        $this->actingAs($this->admin, 'admin')->deleteJson(route('admin.governorates.destroy', $governorate))->assertOk();
        $this->actingAs($this->admin, 'admin')->deleteJson(route('admin.countries.destroy', $country))->assertOk();
    }

    public function test_admin_can_manage_main_and_subcategories_without_accidental_cascade_deletion(): void
    {
        $mainPayload = [
            'icon' => 'device-mobile',
            'sort_order' => 1,
            'is_active' => true,
            'ar' => ['name' => 'الإلكترونيات', 'description' => 'الأجهزة الحديثة'],
            'en' => ['name' => 'Electronics', 'description' => 'Modern devices'],
        ];

        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.main-categories.store'), $mainPayload)
            ->assertOk();

        $main = Category::query()->whereNull('parent_id')->firstOrFail();

        $this->actingAs($this->admin, 'admin')->postJson(route('admin.sub-categories.store'), [
            'parent_id' => $main->id,
            'icon' => 'phone',
            'sort_order' => 2,
            'is_active' => true,
            'ar' => ['name' => 'الهواتف', 'description' => null],
            'en' => ['name' => 'Smartphones', 'description' => null],
        ])->assertOk();

        $sub = Category::query()->where('parent_id', $main->id)->firstOrFail();

        $this->actingAs($this->admin, 'admin')->putJson(route('admin.main-categories.update', $main), [
            ...$mainPayload,
            'sort_order' => 5,
            'ar' => ['name' => 'الإلكترونيات الاستهلاكية', 'description' => 'الأجهزة الحديثة'],
            'en' => ['name' => 'Consumer Electronics', 'description' => 'Modern devices'],
        ])->assertOk();

        $this->assertDatabaseHas('categories', ['id' => $main->id, 'sort_order' => 5]);
        $this->assertDatabaseHas('category_translations', ['category_id' => $main->id, 'locale' => 'en', 'name' => 'Consumer Electronics']);

        $this->actingAs($this->admin, 'admin')->postJson(route('admin.sub-categories.store'), [
            'parent_id' => $sub->id,
            'icon' => null,
            'sort_order' => 3,
            'is_active' => true,
            'ar' => ['name' => 'مستوى ثالث', 'description' => null],
            'en' => ['name' => 'Third level', 'description' => null],
        ])->assertUnprocessable()->assertJsonValidationErrors('parent_id');

        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.main-categories.destroy', $main))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('delete');

        $this->assertDatabaseHas('categories', ['id' => $main->id]);
        $this->assertDatabaseHas('categories', ['id' => $sub->id]);

        $this->actingAs($this->admin, 'admin')->putJson(route('admin.sub-categories.update', $sub), [
            'parent_id' => $main->id,
            'icon' => 'phone',
            'sort_order' => 4,
            'is_active' => false,
            'ar' => ['name' => 'الهواتف المحمولة', 'description' => null],
            'en' => ['name' => 'Mobile phones', 'description' => null],
        ])->assertOk();

        $this->assertDatabaseHas('categories', ['id' => $sub->id, 'is_active' => false]);

        $this->actingAs($this->admin, 'admin')->deleteJson(route('admin.sub-categories.destroy', $sub))->assertOk();
        $this->actingAs($this->admin, 'admin')->deleteJson(route('admin.main-categories.destroy', $main))->assertOk();
    }

    public function test_yajra_datatable_returns_localized_joined_data_without_lazy_loading(): void
    {
        $country = Country::factory()->create([
            'ar' => ['name' => 'مصر'],
            'en' => ['name' => 'Egypt'],
        ]);
        Governorate::factory()->create([
            'country_id' => $country->id,
            'ar' => ['name' => 'القاهرة'],
            'en' => ['name' => 'Cairo'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.governorates.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'columns' => [
                    ['data' => 'id', 'name' => 'governorates.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'name', 'name' => 'governorate_translation.name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'country_name', 'name' => 'country_translation.name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 1, 'dir' => 'asc']],
                'search' => ['value' => 'القاهرة', 'regex' => 'false'],
            ]));

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'القاهرة')
            ->assertJsonPath('data.0.country_name', 'مصر')
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_catalog_create_edit_and_show_modals_render_for_every_entity(): void
    {
        $country = Country::factory()->create();
        $governorate = Governorate::factory()->create(['country_id' => $country->id]);
        $city = City::factory()->create(['governorate_id' => $governorate->id]);
        $main = Category::factory()->create();
        $sub = Category::factory()->subcategory($main)->create();

        $routes = [
            ['admin.countries.create', [], 'admin.countries.edit', $country, 'admin.countries.show'],
            ['admin.governorates.create', [], 'admin.governorates.edit', $governorate, 'admin.governorates.show'],
            ['admin.cities.create', [], 'admin.cities.edit', $city, 'admin.cities.show'],
            ['admin.main-categories.create', [], 'admin.main-categories.edit', $main, 'admin.main-categories.show'],
            ['admin.sub-categories.create', [], 'admin.sub-categories.edit', $sub, 'admin.sub-categories.show'],
        ];

        foreach ($routes as [$createRoute, $createParameters, $editRoute, $entity, $showRoute]) {
            $this->actingAs($this->admin, 'admin')
                ->getJson(route($createRoute, $createParameters))
                ->assertOk()
                ->assertJsonPath('status', true)
                ->assertJsonStructure(['data' => ['html']]);

            $this->actingAs($this->admin, 'admin')
                ->getJson(route($editRoute, $entity))
                ->assertOk()
                ->assertJsonPath('status', true)
                ->assertJsonStructure(['data' => ['html']]);

            $this->actingAs($this->admin, 'admin')
                ->getJson(route($showRoute, $entity))
                ->assertOk()
                ->assertJsonPath('status', true)
                ->assertJsonStructure(['data' => ['html']]);
        }
    }

    public function test_every_catalog_datatable_endpoint_executes_its_optimized_query(): void
    {
        $country = Country::factory()->create();
        $governorate = Governorate::factory()->create(['country_id' => $country->id]);
        City::factory()->create(['governorate_id' => $governorate->id]);
        $main = Category::factory()->create();
        Category::factory()->subcategory($main)->create();

        $endpoints = [
            ['admin.countries.index', 'countries.id'],
            ['admin.governorates.index', 'governorates.id'],
            ['admin.cities.index', 'cities.id'],
            ['admin.main-categories.index', 'categories.id'],
            ['admin.sub-categories.index', 'categories.id'],
        ];

        foreach ($endpoints as [$route, $columnName]) {
            $this->actingAs($this->admin, 'admin')
                ->withHeader('X-Requested-With', 'XMLHttpRequest')
                ->getJson(route($route, [
                    'draw' => 1,
                    'start' => 0,
                    'length' => 10,
                    'columns' => [
                        ['data' => 'id', 'name' => $columnName, 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ],
                    'order' => [['column' => 0, 'dir' => 'desc']],
                    'search' => ['value' => '', 'regex' => 'false'],
                ]))
                ->assertOk()
                ->assertJsonPath('recordsTotal', 1)
                ->assertJsonCount(1, 'data');
        }
    }
}

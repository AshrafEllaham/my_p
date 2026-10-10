<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountTypeEnum;
use App\Enums\AdActionEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\MediaTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Sai\Ad;
use App\Models\Sai\AdPackage;
use App\Models\Sai\Category;
use App\Models\Sai\Store;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class AdPackageTest extends TestCase
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

    public function test_ad_package_dashboard_requires_admin_authentication(): void
    {
        $this->get(route('admin.ad-packages.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_open_ad_packages_page_and_create_modal(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ad-packages.index'))
            ->assertOk()
            ->assertViewIs('admin.catalog.index')
            ->assertSee(__('admin.ad_packages.title'))
            ->assertSee(__('admin.ad_packages.fields.duration_days'));

        $modal = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.ad-packages.create'))
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertStringContainsString('name="ar[name]"', $modal->json('data.html'));
        $this->assertStringContainsString('name="en[name]"', $modal->json('data.html'));
        $this->assertStringContainsString('name="price"', $modal->json('data.html'));
    }

    public function test_admin_can_create_update_and_delete_ad_package_translations(): void
    {
        $payload = $this->payload();

        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ad-packages.store'), $payload)
            ->assertOk()
            ->assertJsonPath('status', true);

        $package = AdPackage::query()->firstOrFail();
        $this->assertDatabaseHas('ad_packages', [
            'id' => $package->id,
            'code' => 'weekly',
            'duration_days' => 7,
            'price' => '350.00',
            'currency' => 'EGP',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('ad_package_translations', [
            'ad_package_id' => $package->id,
            'locale' => 'ar',
            'name' => 'ظهور مميز',
        ]);
        $this->assertDatabaseHas('ad_package_translations', [
            'ad_package_id' => $package->id,
            'locale' => 'en',
            'name' => 'Featured placement',
        ]);

        $updatedPayload = $this->payload([
            'code' => 'monthly',
            'duration_days' => 30,
            'price' => '1200.50',
            'ar' => ['name' => 'ظهور شهري', 'description' => 'إعلان لمدة شهر'],
            'en' => ['name' => 'Monthly placement', 'description' => 'A month-long ad'],
        ]);

        $this->actingAs($this->admin, 'admin')
            ->putJson(route('admin.ad-packages.update', $package), $updatedPayload)
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('ad_packages', [
            'id' => $package->id,
            'code' => 'monthly',
            'duration_days' => 30,
            'price' => '1200.50',
        ]);
        $this->assertDatabaseHas('ad_package_translations', [
            'ad_package_id' => $package->id,
            'locale' => 'en',
            'name' => 'Monthly placement',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.ad-packages.destroy', $package))
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseMissing('ad_packages', ['id' => $package->id]);
        $this->assertDatabaseMissing('ad_package_translations', ['ad_package_id' => $package->id]);
    }

    public function test_ad_package_validation_is_localized_and_code_must_be_unique(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ad-packages.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'duration_days', 'price', 'currency', 'ar.name', 'en.name']);

        AdPackage::query()->create($this->payload());
        $duplicate = $this->payload();
        $duplicate['ar']['name'] = '';

        $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ad-packages.store'), $duplicate)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'ar.name']);
    }

    public function test_package_in_use_cannot_be_deleted_but_can_be_deactivated(): void
    {
        $package = AdPackage::query()->create($this->payload());
        $owner = UserFactory::new()->create(['account_type' => AccountTypeEnum::Store]);
        $category = Category::factory()->create();
        $store = Store::query()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        Ad::query()->create([
            'public_id' => (string) Str::uuid(),
            'store_id' => $store->id,
            'ad_package_id' => $package->id,
            'title' => 'Existing campaign',
            'placement' => AdPlacementEnum::Home,
            'action' => AdActionEnum::ViewStore,
            'media_type' => MediaTypeEnum::Image,
            'media_path' => 'ads/existing.jpg',
            'status' => AdStatusEnum::Draft,
            'cost' => $package->price,
            'currency' => $package->currency,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.ad-packages.destroy', $package))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('delete');

        $this->assertDatabaseHas('ad_packages', ['id' => $package->id]);

        Ad::query()->where('ad_package_id', $package->id)->delete();
        $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.ad-packages.destroy', $package))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('delete');

        $inactivePayload = $this->payload(['is_active' => false]);
        $this->actingAs($this->admin, 'admin')
            ->putJson(route('admin.ad-packages.update', $package), $inactivePayload)
            ->assertOk();

        $this->assertDatabaseHas('ad_packages', ['id' => $package->id, 'is_active' => false]);
    }

    public function test_datatable_returns_the_current_locale_translation(): void
    {
        $package = AdPackage::query()->create($this->payload());

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.ad-packages.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'columns' => [
                    ['data' => 'id', 'name' => 'ad_packages.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'name', 'name' => 'ad_package_translation.name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 0, 'dir' => 'desc']],
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.id', $package->id)
            ->assertJsonPath('data.0.name', $package->translate(app()->getLocale())->name);
    }

    /** @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'code' => 'weekly',
            'duration_days' => 7,
            'price' => '350.00',
            'currency' => 'egp',
            'is_active' => true,
            'ar' => ['name' => 'ظهور مميز', 'description' => 'إعلان لمدة أسبوع'],
            'en' => ['name' => 'Featured placement', 'description' => 'A one-week ad'],
        ], $overrides);
    }
}

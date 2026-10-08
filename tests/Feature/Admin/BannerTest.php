<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Sai\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class BannerTest extends TestCase
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

    public function test_banners_page_requires_admin_authentication(): void
    {
        $this->get(route('admin.banners.index'))->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_open_banners_page_and_see_type_filter_tabs(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.banners.index'))
            ->assertOk()
            ->assertViewIs('admin.catalog.index')
            ->assertSee('data-catalog-filters', false)
            ->assertSee(__('admin.banners.filters.all_types'))
            ->assertSee(__('admin.banners.types.store'))
            ->assertSee(__('admin.banners.types.user'));

        $content = $response->getContent();
        $this->assertStringContainsString('name="type"', $content);
        $this->assertStringContainsString('data-filter-control', $content);
        $this->assertStringContainsString('value="" data-filter-control checked', $content);
    }

    public function test_banners_page_with_type_query_marks_corresponding_filter_tab_as_active(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.banners.index', ['type' => AccountTypeEnum::Store->value]))
            ->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('value="store" data-filter-control checked', $content);
    }

    public function test_datatable_returns_all_banners_when_no_filter_is_applied(): void
    {
        Banner::query()->create(['file' => 'banners/store1.png', 'type' => AccountTypeEnum::Store]);
        Banner::query()->create(['file' => 'banners/store2.png', 'type' => AccountTypeEnum::Store]);
        Banner::query()->create(['file' => 'banners/user1.png', 'type' => AccountTypeEnum::User]);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.banners.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'columns' => [
                    ['data' => 'id', 'name' => 'banners.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'type_label', 'name' => 'banners.type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 0, 'dir' => 'desc']],
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(3, 'data');
    }

    public function test_datatable_filters_banners_by_user_type(): void
    {
        Banner::query()->create(['file' => 'banners/store1.png', 'type' => AccountTypeEnum::Store]);
        Banner::query()->create(['file' => 'banners/store2.png', 'type' => AccountTypeEnum::Store]);
        Banner::query()->create(['file' => 'banners/user1.png', 'type' => AccountTypeEnum::User]);
        Banner::query()->create(['file' => 'banners/user2.png', 'type' => AccountTypeEnum::User]);
        Banner::query()->create(['file' => 'banners/user3.png', 'type' => AccountTypeEnum::User]);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.banners.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'type' => AccountTypeEnum::User->value,
                'columns' => [
                    ['data' => 'id', 'name' => 'banners.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'type_label', 'name' => 'banners.type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 0, 'dir' => 'desc']],
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(3, 'data');

        $data = $response->json('data');
        foreach ($data as $row) {
            $this->assertSame(__('admin.banners.types.user'), $row['type_label']);
        }
    }

    public function test_datatable_filters_banners_by_store_type(): void
    {
        Banner::query()->create(['file' => 'banners/store1.png', 'type' => AccountTypeEnum::Store]);
        Banner::query()->create(['file' => 'banners/store2.png', 'type' => AccountTypeEnum::Store]);
        Banner::query()->create(['file' => 'banners/user1.png', 'type' => AccountTypeEnum::User]);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.banners.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'type' => AccountTypeEnum::Store->value,
                'columns' => [
                    ['data' => 'id', 'name' => 'banners.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                    ['data' => 'type_label', 'name' => 'banners.type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ],
                'order' => [['column' => 0, 'dir' => 'desc']],
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data');

        $data = $response->json('data');
        foreach ($data as $row) {
            $this->assertSame(__('admin.banners.types.store'), $row['type_label']);
        }
    }

    public function test_admin_can_create_update_and_delete_banner(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('banner1.jpg', 800, 400);

        $createResponse = $this->actingAs($this->admin, 'admin')->postJson(route('admin.banners.store'), [
            'file' => $file,
            'type' => AccountTypeEnum::User->value,
        ]);

        $createResponse->assertOk()->assertJsonPath('status', true);
        $banner = Banner::query()->firstOrFail();
        $this->assertSame(AccountTypeEnum::User, $banner->type);
        Storage::disk('public')->assertExists($banner->file);

        $newFile = UploadedFile::fake()->image('banner2.png', 800, 400);
        $updateResponse = $this->actingAs($this->admin, 'admin')->putJson(route('admin.banners.update', $banner), [
            'file' => $newFile,
            'type' => AccountTypeEnum::Store->value,
        ]);

        $updateResponse->assertOk()->assertJsonPath('status', true);
        $banner->refresh();
        $this->assertSame(AccountTypeEnum::Store, $banner->type);
        Storage::disk('public')->assertExists($banner->file);

        $deleteResponse = $this->actingAs($this->admin, 'admin')->deleteJson(route('admin.banners.destroy', $banner));
        $deleteResponse->assertOk()->assertJsonPath('status', true);
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
        Storage::disk('public')->assertMissing($banner->file);
    }
}

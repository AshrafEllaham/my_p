<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Admin\Command;
use Database\Seeders\CommandSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class DeveloperToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleSessionRedirect::class,
        ]);
    }

    public function test_developer_can_open_commands_and_run_a_safe_artisan_command(): void
    {
        $developer = Admin::query()->create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'password123',
            'admin_type' => AdminTypeEnum::Developer,
            'is_active' => true,
        ]);
        $this->seed(CommandSeeder::class);

        $this->actingAs($developer, 'admin')
            ->get(route('admin.developer.commands.index'))
            ->assertOk()
            ->assertSee(__('admin.navigation.developer_tools'))
            ->assertSee('data-admin-datatable', false)
            ->assertSee(__('admin.developer_tools.add_command'))
            ->assertDontSee('developer-page-hero', false);

        $this->actingAs($developer, 'admin')
            ->getJson(route('admin.developer.commands.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);

        $this->actingAs($developer, 'admin')
            ->get(route('admin.developer.terminal.index'))
            ->assertOk()
            ->assertSee(__('admin.developer_tools.terminal_ready'))
            ->assertSee('id="developer-command"', false)
            ->assertSee('aria-controls="developer-command-menu"', false)
            ->assertSee(__('admin.developer_tools.search_commands'))
            ->assertSee('id="developer-custom-command-field" class="developer-terminal__custom-command" hidden', false)
            ->assertSee(__('admin.developer_tools.write_command'))
            ->assertSee('value="php artisan migrate"', false)
            ->assertSee(__('admin.developer_tools.command_format_help'));

        $this->actingAs($developer, 'admin')
            ->postJson(route('admin.developer.terminal.run'), ['command' => 'php artisan about'])
            ->assertOk()
            ->assertJsonPath('successful', true)
            ->assertJsonStructure(['output', 'exit_code', 'successful']);
    }

    public function test_regular_admin_cannot_open_developer_tools(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'admin_type' => AdminTypeEnum::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.index'))
            ->assertOk()
            ->assertDontSee(__('admin.navigation.developer_tools'))
            ->assertDontSee(route('admin.developer.commands.index'))
            ->assertDontSee(route('admin.developer.terminal.index'))
            ->assertDontSee(url(config('log-viewer.route_path', 'log-viewer')));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.developer.commands.index'))
            ->assertForbidden();
    }

    public function test_terminal_rejects_arbitrary_shell_input(): void
    {
        $developer = Admin::query()->create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'password123',
            'admin_type' => AdminTypeEnum::Developer,
            'is_active' => true,
        ]);

        $this->actingAs($developer, 'admin')
            ->postJson(route('admin.developer.terminal.run'), ['command' => 'php artisan migrate && whoami'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('command')
            ->assertJsonPath('errors.command.0', __('messages.validation.developer_command.format'));
    }

    public function test_developer_can_manage_registered_artisan_commands(): void
    {
        $developer = Admin::query()->create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'password123',
            'admin_type' => AdminTypeEnum::Developer,
            'is_active' => true,
        ]);
        $this->seed(CommandSeeder::class);

        $this->actingAs($developer, 'admin')
            ->post(route('admin.developer.commands.store'), ['command' => 'php artisan route:list'])
            ->assertRedirect(route('admin.developer.commands.index'));

        $command = Command::query()->where('command', 'php artisan route:list')->firstOrFail();

        $this->actingAs($developer, 'admin')
            ->put(route('admin.developer.commands.update', $command->id), ['command' => 'php artisan list'])
            ->assertRedirect(route('admin.developer.commands.index'));

        $this->assertDatabaseHas('commands', ['id' => $command->id, 'command' => 'php artisan list']);

        $this->actingAs($developer, 'admin')
            ->delete(route('admin.developer.commands.destroy', $command->id))
            ->assertRedirect(route('admin.developer.commands.index'));

        $this->assertDatabaseMissing('commands', ['id' => $command->id]);
    }

    public function test_terminal_can_run_a_registered_artisan_command_not_saved_in_the_picker(): void
    {
        $developer = Admin::query()->create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'password123',
            'admin_type' => AdminTypeEnum::Developer,
            'is_active' => true,
        ]);

        $this->actingAs($developer, 'admin')
            ->postJson(route('admin.developer.terminal.run'), ['command' => 'php artisan list'])
            ->assertOk()
            ->assertJsonPath('successful', true);
    }

    public function test_developer_cannot_save_an_unregistered_artisan_command(): void
    {
        $developer = Admin::query()->create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'password123',
            'admin_type' => AdminTypeEnum::Developer,
            'is_active' => true,
        ]);

        $this->actingAs($developer, 'admin')
            ->from(route('admin.developer.commands.index'))
            ->post(route('admin.developer.commands.store'), ['command' => 'php artisan command:does-not-exist'])
            ->assertSessionHasErrors('command');
    }

    public function test_log_viewer_is_restricted_to_developer_accounts(): void
    {
        $this->get('/log-viewer')->assertForbidden();

        $admin = Admin::query()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'admin_type' => AdminTypeEnum::Admin,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')->get('/log-viewer')->assertForbidden();
    }

    public function test_command_bottom_sheets_and_ajax_crud(): void
    {
        $developer = Admin::factory()->create(['admin_type' => AdminTypeEnum::Developer]);
        $this->actingAs($developer, 'admin');

        $this->getJson(route('admin.developer.commands.create'))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertSee('data-catalog-form');

        $this->postJson(route('admin.developer.commands.store'), ['command' => 'php artisan about'])
            ->assertOk()
            ->assertJsonPath('message', __('admin.developer_tools.command_created'));

        $command = Command::query()->where('command', 'php artisan about')->firstOrFail();
        $this->getJson(route('admin.developer.commands.edit', $command->id))
            ->assertOk()
            ->assertSee('php artisan about')
            ->assertSee('data-form-errors');

        $this->putJson(route('admin.developer.commands.update', $command->id), ['command' => 'php artisan list'])
            ->assertOk()
            ->assertJsonPath('message', __('admin.developer_tools.command_updated'));
        $this->assertDatabaseHas('commands', ['id' => $command->id, 'command' => 'php artisan list']);

        $this->deleteJson(route('admin.developer.commands.destroy', $command->id))
            ->assertOk()
            ->assertJsonPath('message', __('admin.developer_tools.command_deleted'));
        $this->assertDatabaseMissing('commands', ['id' => $command->id]);
    }

    public function test_command_sheet_validation_is_translated_in_both_languages(): void
    {
        $developer = Admin::factory()->create(['admin_type' => AdminTypeEnum::Developer]);
        $this->actingAs($developer, 'admin');

        foreach (['ar', 'en'] as $locale) {
            app()->setLocale($locale);
            LaravelLocalization::setLocale($locale);
            $this->withSession(['locale' => $locale])
                ->postJson(route('admin.developer.commands.store'), ['command' => 'php artisan list && whoami'])
                ->assertUnprocessable()
                ->assertJsonPath('errors.command.0', trans('messages.validation.developer_command.format', [], $locale));

            $this->postJson(route('admin.developer.commands.store'), [])
                ->assertUnprocessable()
                ->assertJsonPath('errors.command.0', trans('messages.validation.developer_command.required', [], $locale));

            $this->get(route('admin.developer.commands.index'))
                ->assertOk()
                ->assertSee('lang="'.$locale.'"', false)
                ->assertSee(trans('admin.developer_tools.add_command', [], $locale));
            $this->get(route('admin.developer.terminal.index'))
                ->assertOk()
                ->assertSee('dir="'.($locale === 'ar' ? 'rtl' : 'ltr').'"', false)
                ->assertDontSee('developer-page-hero', false)
                ->assertSee(trans('admin.developer_tools.write_command', [], $locale))
                ->assertSee(trans('admin.developer_tools.custom_command', [], $locale));
        }
    }

    public function test_regular_admin_cannot_open_command_sheets_or_mutate_commands(): void
    {
        $admin = Admin::factory()->create(['admin_type' => AdminTypeEnum::Admin]);
        $this->actingAs($admin, 'admin');

        $this->getJson(route('admin.developer.commands.create'))->assertForbidden();
        $this->getJson(route('admin.developer.commands.edit', 1))->assertForbidden();
        $this->postJson(route('admin.developer.commands.store'), ['command' => 'php artisan about'])->assertForbidden();
        $this->putJson(route('admin.developer.commands.update', 1), ['command' => 'php artisan about'])->assertForbidden();
        $this->deleteJson(route('admin.developer.commands.destroy', 1))->assertForbidden();
    }
}

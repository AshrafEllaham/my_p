<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Admin\Command;
use Database\Seeders\CommandSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('php artisan migrate')
            ->assertSee(__('admin.developer_tools.command_list'))
            ->assertSee(__('admin.developer_tools.add_command'))
            ->assertSee(__('admin.developer_tools.edit_command'))
            ->assertSee(__('admin.developer_tools.delete_command'));

        $this->actingAs($developer, 'admin')
            ->get(route('admin.developer.terminal.index'))
            ->assertOk()
            ->assertSee(__('admin.developer_tools.terminal_ready'))
            ->assertSee('developer-command-options')
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
}

<?php

namespace App\Services\Admin;

use App\Models\Admin\Command;
use App\Repositories\Admin\CommandRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class DeveloperToolsService
{
    public function __construct(private readonly CommandRepository $commands) {}

    /** @return Collection<int, Command> */
    public function getCommands(): Collection
    {
        return $this->commands->listAll();
    }

    public function commandListQuery(): Builder
    {
        return $this->commands->listQuery();
    }

    public function findCommand(int $id): Command
    {
        return $this->commands->findCommand($id);
    }

    public function createCommand(string $command): void
    {
        $this->assertRegisteredCommand($command);
        $this->commands->store(['command' => $command]);
    }

    public function updateCommand(int $id, string $command): void
    {
        $this->assertRegisteredCommand($command);
        $this->commands->updateCommand($id, $command);
    }

    public function deleteCommand(int $id): void
    {
        $this->commands->deleteCommand($id);
    }

    /** @return array{output: string, exit_code: int|null, successful: bool} */
    public function run(string $command): array
    {
        $arguments = $this->parseCommand($command);

        if ($arguments === null || ! array_key_exists($arguments[2], Artisan::all())) {
            throw ValidationException::withMessages([
                'command' => [__('messages.validation.developer_command.unavailable')],
            ]);
        }

        $process = new Process([PHP_BINARY, base_path('artisan'), ...array_slice($arguments, 2)], base_path());
        $process->setTimeout(120);

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            return [
                'output' => __('admin.developer_tools.command_timed_out'),
                'exit_code' => $process->getExitCode(),
                'successful' => false,
            ];
        }

        $output = trim($process->getOutput().$process->getErrorOutput());

        return [
            'output' => mb_substr($output ?: __('admin.developer_tools.command_no_output'), 0, 64000),
            'exit_code' => $process->getExitCode(),
            'successful' => $process->isSuccessful(),
        ];
    }

    private function assertRegisteredCommand(string $command): void
    {
        $arguments = $this->parseCommand($command);

        if ($arguments === null || ! array_key_exists($arguments[2], Artisan::all())) {
            throw ValidationException::withMessages([
                'command' => [__('messages.validation.developer_command.unavailable')],
            ]);
        }
    }

    /** @return list<string>|null */
    private function parseCommand(string $command): ?array
    {
        if (! preg_match('~^php artisan [a-zA-Z0-9:_-]+(?: [a-zA-Z0-9_./:=,@+\\\\-]+)*$~', $command)) {
            return null;
        }

        $arguments = preg_split('/\s+/', trim($command));

        return $arguments !== false && count($arguments) >= 3 ? array_values($arguments) : null;
    }
}

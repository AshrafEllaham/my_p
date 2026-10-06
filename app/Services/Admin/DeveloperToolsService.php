<?php

namespace App\Services\Admin;

use App\Enums\DeveloperCommandEnum;
use App\Models\Admin\Command;
use App\Repositories\Admin\CommandRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class DeveloperToolsService
{
    public function __construct(private readonly CommandRepository $commands) {}

    /** @return Collection<int, Command> */
    public function getCommands(): Collection
    {
        return $this->commands->listAllowed(self::supportedCommands());
    }

    /** @return list<string> */
    public static function supportedCommands(): array
    {
        return array_map(static fn (DeveloperCommandEnum $command): string => $command->value, DeveloperCommandEnum::cases());
    }

    /** @return array{output: string, exit_code: int|null, successful: bool} */
    public function run(string $command): array
    {
        $developerCommand = DeveloperCommandEnum::tryFrom($command);

        if ($developerCommand === null) {
            throw ValidationException::withMessages([
                'command' => [__('admin.developer_tools.validation.command_unavailable')],
            ]);
        }

        $process = new Process([PHP_BINARY, base_path('artisan'), ...$developerCommand->arguments()], base_path());
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
}

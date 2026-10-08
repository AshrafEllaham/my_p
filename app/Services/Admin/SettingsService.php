<?php

namespace App\Services\Admin;

use App\Models\Sai\Settings;
use App\Repositories\Sai\SettingsRepository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class SettingsService
{
    private const FILE_FIELDS = ['fav_icon', 'logo_header', 'logo_footer'];

    public function __construct(
        private readonly SettingsRepository $repository,
        private readonly DatabaseManager $database,
        private readonly FilesystemFactory $filesystem,
    ) {}

    public function get(): ?Settings
    {
        return $this->repository->getSingleton();
    }

    /** @param array<string, mixed> $data */
    public function save(array $data): Settings
    {
        $newPaths = [];
        $oldPaths = [];

        foreach (self::FILE_FIELDS as $field) {
            $file = $data[$field] ?? null;
            unset($data[$field]);

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $this->filesystem->disk('public')->putFile('settings', $file);
            if (! is_string($path)) {
                $this->deleteFiles($newPaths);
                throw new RuntimeException(__('admin.site_settings.messages.upload_failed'));
            }

            $newPaths[$field] = $path;
            $data[$field] = $path;
        }

        try {
            $currentSettings = $this->repository->getSingleton();
            foreach (array_keys($newPaths) as $field) {
                $oldPath = $currentSettings?->{$field};
                if (is_string($oldPath) && $oldPath !== '') {
                    $oldPaths[] = $oldPath;
                }
            }

            $settings = $this->database->transaction(
                fn (): Settings => $this->repository->saveSingleton($data),
            );
        } catch (Throwable $exception) {
            $this->deleteFiles(array_values($newPaths));
            throw $exception;
        }

        $this->deleteFiles($oldPaths);

        return $settings;
    }

    /** @param array<int|string, string> $paths */
    private function deleteFiles(array $paths): void
    {
        if ($paths !== []) {
            $this->filesystem->disk('public')->delete(array_values($paths));
        }
    }
}

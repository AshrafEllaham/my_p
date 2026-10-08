<?php

namespace App\Services\Admin;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Banner;
use App\Repositories\Admin\BannerRepository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class BannerService
{
    public function __construct(
        private readonly BannerRepository $repository,
        private readonly DatabaseManager $database,
        private readonly FilesystemFactory $filesystem,
    ) {}

    public function dataTableQuery(?string $type = null): Builder
    {
        $accountType = $type !== null && $type !== ''
            ? AccountTypeEnum::tryFrom($type)
            : null;

        return $this->repository->dataTableQuery($accountType);
    }

    public function find(int $id): Banner
    {
        return $this->repository->findForAdmin($id);
    }

    /** @param array{file: UploadedFile, type: string} $data */
    public function create(array $data): Banner
    {
        $path = $this->storeFile($data['file']);

        try {
            return $this->database->transaction(fn (): Banner => $this->repository->create([
                'file' => $path,
                'type' => $data['type'],
            ]));
        } catch (Throwable $exception) {
            $this->filesystem->disk('public')->delete($path);

            throw $exception;
        }
    }

    /** @param array{file?: UploadedFile|null, type: string} $data */
    public function update(int $id, array $data): Banner
    {
        $banner = $this->repository->findForAdmin($id);
        $file = $data['file'] ?? null;
        $newPath = $file instanceof UploadedFile ? $this->storeFile($file) : null;
        $oldPath = $banner->file;
        $changes = ['type' => $data['type']];

        if ($newPath !== null) {
            $changes['file'] = $newPath;
        }

        try {
            $updatedBanner = $this->database->transaction(
                fn (): Banner => $this->repository->updateBanner($id, $changes),
            );
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                $this->filesystem->disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath !== null) {
            $this->filesystem->disk('public')->delete($oldPath);
        }

        return $updatedBanner;
    }

    public function delete(int $id): void
    {
        $banner = $this->repository->findForAdmin($id);

        $this->database->transaction(fn () => $this->repository->deleteBanner($id));
        $this->filesystem->disk('public')->delete($banner->file);
    }

    private function storeFile(UploadedFile $file): string
    {
        $path = $this->filesystem->disk('public')->putFile('banners', $file);

        if (! is_string($path)) {
            throw new RuntimeException(__('admin.banners.messages.upload_failed'));
        }

        return $path;
    }
}

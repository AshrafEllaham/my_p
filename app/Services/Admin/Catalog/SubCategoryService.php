<?php

namespace App\Services\Admin\Catalog;

use App\Models\Sai\Category;
use App\Repositories\Admin\Catalog\CategoryRepository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SubCategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly DatabaseManager $database,
        private readonly FilesystemFactory $filesystem,
    ) {}

    public function dataTableQuery(string $locale): Builder
    {
        return $this->repository->dataTableQuery($locale, true);
    }

    /** @return Collection<int, Category> */
    public function mainCategoryOptions(string $locale): Collection
    {
        return $this->repository->mainOptions($locale);
    }

    public function find(int $id): Category
    {
        return $this->repository->findSubForAdmin($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Category
    {
        $image = $data['image'] ?? null;
        unset($data['image']);
        $path = $image instanceof UploadedFile ? $this->storeImage($image) : null;

        if ($path !== null) {
            $data['image'] = $path;
        }

        try {
            return $this->database->transaction(function () use ($data): Category {
                $this->ensureMainCategory((int) $data['parent_id']);

                return $this->repository->create($data);
            });
        } catch (Throwable $exception) {
            if ($path !== null) {
                $this->filesystem->disk('public')->delete($path);
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Category
    {
        $category = $this->repository->findSubForAdmin($id);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $newPath = $image instanceof UploadedFile ? $this->storeImage($image) : null;
        $oldPath = $category->image;

        if ($newPath !== null) {
            $data['image'] = $newPath;
        }

        try {
            $updated = $this->database->transaction(function () use ($id, $data): Category {
                $this->ensureMainCategory((int) $data['parent_id']);
                $category = $this->repository->findSubForAdmin($id);

                return $this->repository->updateCategory($category, $data);
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                $this->filesystem->disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath !== null && $oldPath !== null) {
            $this->filesystem->disk('public')->delete($oldPath);
        }

        return $updated;
    }

    public function delete(int $id): void
    {
        $category = $this->repository->findSubForAdmin($id);

        $this->database->transaction(function () use ($id): void {
            $this->repository->delete($id);
        });

        if ($category->image !== null) {
            $this->filesystem->disk('public')->delete($category->image);
        }
    }

    private function ensureMainCategory(int $parentId): void
    {
        if (! $this->repository->mainExists($parentId)) {
            throw ValidationException::withMessages([
                'parent_id' => __('admin.catalog.validation.parent_main'),
            ]);
        }
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $this->filesystem->disk('public')->putFile('categories', $image);

        if (! is_string($path)) {
            throw new RuntimeException(__('admin.catalog.messages.upload_failed'));
        }

        return $path;
    }
}

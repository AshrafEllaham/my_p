<?php

namespace App\Services\Admin\Catalog;

use App\Models\Sai\Category;
use App\Repositories\Admin\Catalog\CategoryRepository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;
use Illuminate\Validation\ValidationException;

class MainCategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly DatabaseManager $database,
        private readonly FilesystemFactory $filesystem,
    ) {}

    public function dataTableQuery(string $locale): Builder
    {
        return $this->repository->dataTableQuery($locale, false);
    }

    public function find(int $id): Category
    {
        return $this->repository->findMainForAdmin($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Category
    {
        $data['parent_id'] = null;
        $image = $data['image'] ?? null;
        unset($data['image']);
        $path = $image instanceof UploadedFile ? $this->storeImage($image) : null;

        if ($path !== null) {
            $data['image'] = $path;
        }

        try {
            return $this->database->transaction(fn (): Category => $this->repository->create($data));
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
        $category = $this->repository->findMainForAdmin($id);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $newPath = $image instanceof UploadedFile ? $this->storeImage($image) : null;
        $oldPath = $category->image;
        $data['parent_id'] = null;

        if ($newPath !== null) {
            $data['image'] = $newPath;
        }

        try {
            $updated = $this->database->transaction(function () use ($id, $data): Category {
                $category = $this->repository->findMainForAdmin($id);

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
        $category = $this->repository->findMainForAdmin($id);

        $this->database->transaction(function () use ($id): void {
            if ($this->repository->hasChildren($id)) {
                throw ValidationException::withMessages([
                    'delete' => __('admin.catalog.errors.category_has_children'),
                ]);
            }

            $this->repository->delete($id);
        });

        if ($category->image !== null) {
            $this->filesystem->disk('public')->delete($category->image);
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

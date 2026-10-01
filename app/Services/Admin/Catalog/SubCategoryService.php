<?php

namespace App\Services\Admin\Catalog;

use App\Models\Twenty\Category;
use App\Repositories\Admin\Catalog\CategoryRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SubCategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly DatabaseManager $database,
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
        return $this->database->transaction(function () use ($data): Category {
            $this->ensureMainCategory((int) $data['parent_id']);

            return $this->repository->create($data);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Category
    {
        return $this->database->transaction(function () use ($id, $data): Category {
            $this->ensureMainCategory((int) $data['parent_id']);
            $category = $this->repository->findSubForAdmin($id);

            return $this->repository->updateCategory($category, $data);
        });
    }

    public function delete(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $this->repository->findSubForAdmin($id);
            $this->repository->delete($id);
        });
    }

    private function ensureMainCategory(int $parentId): void
    {
        if (! $this->repository->mainExists($parentId)) {
            throw ValidationException::withMessages([
                'parent_id' => __('admin.catalog.validation.parent_main'),
            ]);
        }
    }
}

<?php

namespace App\Services\Admin\Catalog;

use App\Models\Sai\Category;
use App\Repositories\Admin\Catalog\CategoryRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class MainCategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly DatabaseManager $database,
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

        return $this->database->transaction(fn (): Category => $this->repository->create($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Category
    {
        return $this->database->transaction(function () use ($id, $data): Category {
            $category = $this->repository->findMainForAdmin($id);
            $data['parent_id'] = null;

            return $this->repository->updateCategory($category, $data);
        });
    }

    public function delete(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $this->repository->findMainForAdmin($id);

            if ($this->repository->hasChildren($id)) {
                throw ValidationException::withMessages([
                    'delete' => __('admin.catalog.errors.category_has_children'),
                ]);
            }

            $this->repository->delete($id);
        });
    }
}

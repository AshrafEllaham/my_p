<?php

namespace App\Repositories\Admin\Catalog;

use App\Models\Twenty\Category;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryRepository extends MainRepository
{
    public function __construct(Category $category)
    {
        $this->model = $category;
    }

    public function dataTableQuery(string $locale, bool $subCategories): Builder
    {
        $query = $this->model->newQuery()
            ->leftJoin('category_translations as category_translation', function ($join) use ($locale): void {
                $join->on('category_translation.category_id', '=', 'categories.id')
                    ->where('category_translation.locale', $locale);
            })
            ->leftJoin('category_translations as parent_translation', function ($join) use ($locale): void {
                $join->on('parent_translation.category_id', '=', 'categories.parent_id')
                    ->where('parent_translation.locale', $locale);
            })
            ->select([
                'categories.id',
                'categories.parent_id',
                'categories.icon',
                'categories.is_active',
                'categories.sort_order',
                'categories.created_at',
                'category_translation.name',
                'parent_translation.name as parent_name',
            ]);

        if ($subCategories) {
            return $query->whereNotNull('categories.parent_id');
        }

        return $query->whereNull('categories.parent_id')->withCount('children');
    }

    /** @return Collection<int, Category> */
    public function mainOptions(string $locale): Collection
    {
        return $this->model->newQuery()
            ->join('category_translations as category_translation', function ($join) use ($locale): void {
                $join->on('category_translation.category_id', '=', 'categories.id')
                    ->where('category_translation.locale', $locale);
            })
            ->whereNull('categories.parent_id')
            ->select(['categories.id', 'category_translation.name'])
            ->orderBy('categories.sort_order')
            ->orderBy('category_translation.name')
            ->get();
    }

    public function findMainForAdmin(int $id): Category
    {
        return $this->model->newQuery()
            ->whereNull('parent_id')
            ->with('translations')
            ->withCount('children')
            ->findOrFail($id);
    }

    public function findSubForAdmin(int $id): Category
    {
        return $this->model->newQuery()
            ->whereNotNull('parent_id')
            ->with(['translations', 'parent.translations'])
            ->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Category
    {
        /** @var Category $category */
        $category = $this->store($data);

        return $category->load(['translations', 'parent.translations']);
    }

    /** @param array<string, mixed> $data */
    public function updateCategory(Category $category, array $data): Category
    {
        $category->fill($data);
        $category->save();

        return $category->refresh()->load(['translations', 'parent.translations']);
    }

    public function hasChildren(int $id): bool
    {
        return $this->model->newQuery()->whereKey($id)->whereHas('children')->exists();
    }

    public function mainExists(int $id): bool
    {
        return $this->model->newQuery()->whereKey($id)->whereNull('parent_id')->exists();
    }
}

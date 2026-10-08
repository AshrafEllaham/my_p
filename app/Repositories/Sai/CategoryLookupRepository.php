<?php

namespace App\Repositories\Sai;

use App\Models\Sai\Category;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class CategoryLookupRepository extends MainRepository
{
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function mainCategories(string $locale): Builder
    {
        return $this->baseQuery($locale)->whereNull('parent_id');
    }

    public function subCategories(string $locale, ?int $mainCategoryId): Builder
    {
        return $this->baseQuery($locale)
            ->whereNotNull('parent_id')
            ->whereHas('parent', fn (Builder $query) => $query->where('is_active', true))
            ->when($mainCategoryId !== null, fn (Builder $query) => $query->where('parent_id', $mainCategoryId));
    }

    private function baseQuery(string $locale): Builder
    {
        return $this->getModel()->newQuery()
            ->select(['id', 'parent_id', 'image', 'sort_order'])
            ->with(['translations' => fn ($query) => $query
                ->select(['id', 'category_id', 'locale', 'name', 'description'])
                ->where('locale', $locale)])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}

<?php

namespace App\Services\Sai;

use App\Repositories\Sai\CategoryLookupRepository;
use Illuminate\Database\Eloquent\Builder;

class UserHomeCategoryService
{
    public function __construct(private readonly CategoryLookupRepository $categories) {}

    public function mainCategories(string $locale, ?string $search = null): Builder
    {
        return $this->categories->mainCategories($locale, $search);
    }

    public function subCategories(string $locale, int $mainCategoryId): Builder
    {
        return $this->categories->subCategories($locale, $mainCategoryId);
    }
}

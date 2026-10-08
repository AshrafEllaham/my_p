<?php

namespace App\Services\Sai;

use App\Repositories\Sai\CategoryLookupRepository;
use App\Repositories\Sai\CityLookupRepository;
use App\Repositories\Sai\CountryLookupRepository;
use App\Repositories\Sai\GovernorateLookupRepository;
use Illuminate\Database\Eloquent\Builder;

class CatalogLookupService
{
    public function __construct(
        private readonly CategoryLookupRepository $categories,
        private readonly CountryLookupRepository $countries,
        private readonly GovernorateLookupRepository $governorates,
        private readonly CityLookupRepository $cities,
    ) {}

    public function mainCategories(string $locale): Builder
    {
        return $this->categories->mainCategories($locale);
    }

    public function subCategories(string $locale, ?int $mainCategoryId): Builder
    {
        return $this->categories->subCategories($locale, $mainCategoryId);
    }

    public function countries(string $locale): Builder
    {
        return $this->countries->activeCountries($locale);
    }

    public function governorates(string $locale, ?int $countryId): Builder
    {
        return $this->governorates->activeGovernorates($locale, $countryId);
    }

    public function cities(string $locale, ?int $governorateId): Builder
    {
        return $this->cities->activeCities($locale, $governorateId);
    }
}

<?php

namespace App\Repositories\Sai;

use App\Models\Sai\Country;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class CountryLookupRepository extends MainRepository
{
    public function __construct(Country $model)
    {
        $this->model = $model;
    }

    public function activeCountries(string $locale): Builder
    {
        return $this->getModel()->newQuery()
            ->select(['id', 'code', 'phone_code', 'flag'])
            ->with(['translations' => fn ($query) => $query
                ->select(['id', 'country_id', 'locale', 'name'])
                ->where('locale', $locale)])
            ->where('is_active', true)
            ->orderBy('id');
    }
}

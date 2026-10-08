<?php

namespace App\Repositories\Sai;

use App\Models\Sai\City;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class CityLookupRepository extends MainRepository
{
    public function __construct(City $model)
    {
        $this->model = $model;
    }

    public function activeCities(string $locale, ?int $governorateId): Builder
    {
        return $this->getModel()->newQuery()
            ->select(['id', 'governorate_id'])
            ->with(['translations' => fn ($query) => $query
                ->select(['id', 'city_id', 'locale', 'name'])
                ->where('locale', $locale)])
            ->where('is_active', true)
            ->whereHas('governorate', fn (Builder $query) => $query->where('is_active', true))
            ->when($governorateId !== null, fn (Builder $query) => $query->where('governorate_id', $governorateId))
            ->orderBy('id');
    }
}

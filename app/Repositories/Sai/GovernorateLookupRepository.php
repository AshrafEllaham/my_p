<?php

namespace App\Repositories\Sai;

use App\Models\Sai\Governorate;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class GovernorateLookupRepository extends MainRepository
{
    public function __construct(Governorate $model)
    {
        $this->model = $model;
    }

    public function activeGovernorates(string $locale, ?int $countryId): Builder
    {
        return $this->getModel()->newQuery()
            ->select(['id', 'country_id'])
            ->with(['translations' => fn ($query) => $query
                ->select(['id', 'governorate_id', 'locale', 'name'])
                ->where('locale', $locale)])
            ->where('is_active', true)
            ->whereHas('country', fn (Builder $query) => $query->where('is_active', true))
            ->when($countryId !== null, fn (Builder $query) => $query->where('country_id', $countryId))
            ->orderBy('id');
    }
}

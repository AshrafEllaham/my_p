<?php

namespace App\Repositories\Admin\Catalog;

use App\Models\Twenty\Country;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CountryRepository extends MainRepository
{
    public function __construct(Country $country)
    {
        $this->model = $country;
    }

    public function dataTableQuery(string $locale): Builder
    {
        return $this->model->newQuery()
            ->leftJoin('country_translations as country_translation', function ($join) use ($locale): void {
                $join->on('country_translation.country_id', '=', 'countries.id')
                    ->where('country_translation.locale', $locale);
            })
            ->select([
                'countries.id',
                'countries.code',
                'countries.phone_code',
                'countries.flag',
                'countries.is_active',
                'countries.created_at',
                'country_translation.name',
            ])
            ->withCount('governorates');
    }

    /** @return Collection<int, Country> */
    public function options(string $locale): Collection
    {
        return $this->model->newQuery()
            ->join('country_translations as country_translation', function ($join) use ($locale): void {
                $join->on('country_translation.country_id', '=', 'countries.id')
                    ->where('country_translation.locale', $locale);
            })
            ->select(['countries.id', 'country_translation.name'])
            ->orderBy('country_translation.name')
            ->get();
    }

    public function findForAdmin(int $id): Country
    {
        return $this->model->newQuery()
            ->with('translations')
            ->withCount('governorates')
            ->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Country
    {
        /** @var Country $country */
        $country = $this->store($data);

        return $country->load('translations');
    }

    /** @param array<string, mixed> $data */
    public function updateCountry(int $id, array $data): Country
    {
        $country = $this->findForAdmin($id);
        $country->fill($data);
        $country->save();

        return $country->refresh()->load('translations');
    }

    public function hasGovernorates(int $id): bool
    {
        return $this->model->newQuery()->whereKey($id)->whereHas('governorates')->exists();
    }
}

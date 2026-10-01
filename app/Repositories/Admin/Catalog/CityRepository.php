<?php

namespace App\Repositories\Admin\Catalog;

use App\Models\Twenty\City;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class CityRepository extends MainRepository
{
    public function __construct(City $city)
    {
        $this->model = $city;
    }

    public function dataTableQuery(string $locale): Builder
    {
        return $this->model->newQuery()
            ->leftJoin('city_translations as city_translation', function ($join) use ($locale): void {
                $join->on('city_translation.city_id', '=', 'cities.id')
                    ->where('city_translation.locale', $locale);
            })
            ->join('governorates', 'governorates.id', '=', 'cities.governorate_id')
            ->leftJoin('governorate_translations as governorate_translation', function ($join) use ($locale): void {
                $join->on('governorate_translation.governorate_id', '=', 'cities.governorate_id')
                    ->where('governorate_translation.locale', $locale);
            })
            ->leftJoin('country_translations as country_translation', function ($join) use ($locale): void {
                $join->on('country_translation.country_id', '=', 'governorates.country_id')
                    ->where('country_translation.locale', $locale);
            })
            ->select([
                'cities.id',
                'cities.governorate_id',
                'cities.is_active',
                'cities.created_at',
                'city_translation.name',
                'governorate_translation.name as governorate_name',
                'country_translation.name as country_name',
            ]);
    }

    public function findForAdmin(int $id): City
    {
        return $this->model->newQuery()
            ->with([
                'translations',
                'governorate.translations',
                'governorate.country.translations',
            ])
            ->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): City
    {
        /** @var City $city */
        $city = $this->store($data);

        return $city->load([
            'translations',
            'governorate.translations',
            'governorate.country.translations',
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateCity(int $id, array $data): City
    {
        $city = $this->findForAdmin($id);
        $city->fill($data);
        $city->save();

        return $city->refresh()->load([
            'translations',
            'governorate.translations',
            'governorate.country.translations',
        ]);
    }
}

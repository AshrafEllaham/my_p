<?php

namespace App\Repositories\Admin\Catalog;

use App\Models\Twenty\Governorate;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GovernorateRepository extends MainRepository
{
    public function __construct(Governorate $governorate)
    {
        $this->model = $governorate;
    }

    public function dataTableQuery(string $locale): Builder
    {
        return $this->model->newQuery()
            ->leftJoin('governorate_translations as governorate_translation', function ($join) use ($locale): void {
                $join->on('governorate_translation.governorate_id', '=', 'governorates.id')
                    ->where('governorate_translation.locale', $locale);
            })
            ->leftJoin('country_translations as country_translation', function ($join) use ($locale): void {
                $join->on('country_translation.country_id', '=', 'governorates.country_id')
                    ->where('country_translation.locale', $locale);
            })
            ->select([
                'governorates.id',
                'governorates.country_id',
                'governorates.is_active',
                'governorates.created_at',
                'governorate_translation.name',
                'country_translation.name as country_name',
            ])
            ->withCount('cities');
    }

    /** @return Collection<int, Governorate> */
    public function options(string $locale): Collection
    {
        return $this->model->newQuery()
            ->join('governorate_translations as governorate_translation', function ($join) use ($locale): void {
                $join->on('governorate_translation.governorate_id', '=', 'governorates.id')
                    ->where('governorate_translation.locale', $locale);
            })
            ->join('country_translations as country_translation', function ($join) use ($locale): void {
                $join->on('country_translation.country_id', '=', 'governorates.country_id')
                    ->where('country_translation.locale', $locale);
            })
            ->select([
                'governorates.id',
                'governorates.country_id',
                'governorate_translation.name',
                'country_translation.name as country_name',
            ])
            ->orderBy('country_translation.name')
            ->orderBy('governorate_translation.name')
            ->get();
    }

    public function findForAdmin(int $id): Governorate
    {
        return $this->model->newQuery()
            ->with(['translations', 'country.translations'])
            ->withCount('cities')
            ->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Governorate
    {
        /** @var Governorate $governorate */
        $governorate = $this->store($data);

        return $governorate->load(['translations', 'country.translations']);
    }

    /** @param array<string, mixed> $data */
    public function updateGovernorate(int $id, array $data): Governorate
    {
        $governorate = $this->findForAdmin($id);
        $governorate->fill($data);
        $governorate->save();

        return $governorate->refresh()->load(['translations', 'country.translations']);
    }

    public function hasCities(int $id): bool
    {
        return $this->model->newQuery()->whereKey($id)->whereHas('cities')->exists();
    }
}

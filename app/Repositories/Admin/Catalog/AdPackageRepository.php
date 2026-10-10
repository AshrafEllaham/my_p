<?php

namespace App\Repositories\Admin\Catalog;

use App\Models\Sai\AdPackage;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class AdPackageRepository extends MainRepository
{
    public function __construct(AdPackage $model)
    {
        $this->model = $model;
    }

    public function dataTableQuery(string $locale): Builder
    {
        return $this->getModel()->newQuery()
            ->leftJoin('ad_package_translations as ad_package_translation', function ($join) use ($locale): void {
                $join->on('ad_package_translation.ad_package_id', '=', 'ad_packages.id')
                    ->where('ad_package_translation.locale', $locale);
            })
            ->select([
                'ad_packages.id',
                'ad_packages.code',
                'ad_packages.duration_days',
                'ad_packages.price',
                'ad_packages.currency',
                'ad_packages.is_active',
                'ad_packages.created_at',
                'ad_package_translation.name',
                'ad_package_translation.description',
            ])
            ->orderByDesc('ad_packages.id');
    }

    public function findForAdmin(int $id): AdPackage
    {
        return $this->getModel()->newQuery()->with('translations')->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): AdPackage
    {
        /** @var AdPackage $package */
        $package = $this->store($data);

        return $package->load('translations');
    }

    /** @param array<string, mixed> $data */
    public function updatePackage(int $id, array $data): AdPackage
    {
        $package = $this->findForAdmin($id);
        $package->fill($data);
        $package->save();

        return $package->refresh()->load('translations');
    }

    public function hasAds(int $id): bool
    {
        return $this->getModel()->newQuery()
            ->whereKey($id)
            ->whereHas('ads', fn (Builder $query) => $query->withTrashed())
            ->exists();
    }

    public function deletePackage(int $id): void
    {
        $this->delete($id);
    }
}

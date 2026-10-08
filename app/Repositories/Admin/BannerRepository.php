<?php

namespace App\Repositories\Admin;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Banner;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class BannerRepository extends MainRepository
{
    public function __construct(Banner $model)
    {
        $this->model = $model;
    }

    public function dataTableQuery(?AccountTypeEnum $type = null): Builder
    {
        return $this->getModel()->newQuery()
            ->select(['id', 'file', 'type'])
            ->when($type !== null, fn (Builder $query) => $query->where('type', $type->value))
            ->orderByDesc('id');
    }

    public function findForAdmin(int $id): Banner
    {
        return $this->getModel()->newQuery()->findOrFail($id);
    }

    /** @param array{file: string, type: string} $data */
    public function create(array $data): Banner
    {
        /** @var Banner $banner */
        $banner = $this->store($data);

        return $banner;
    }

    /** @param array{file?: string, type?: string} $data */
    public function updateBanner(int $id, array $data): Banner
    {
        $banner = $this->findForAdmin($id);
        $banner->fill($data);
        $banner->save();

        return $banner->refresh();
    }

    public function deleteBanner(int $id): void
    {
        $this->findForAdmin($id)->delete();
    }
}

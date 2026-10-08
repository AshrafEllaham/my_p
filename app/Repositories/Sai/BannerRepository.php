<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Banner;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BannerRepository extends MainRepository
{
    public function __construct(Banner $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function queryForType(AccountTypeEnum $type): Builder
    {
        return $this->query()->where('type', $type->value);
    }

    /** @return Collection<int, Banner> */
    public function getForType(?AccountTypeEnum $type): Collection
    {
        $query = $this->getModel()->newQuery()
            ->select(['id', 'file', 'type'])
            ->orderBy('id');

        if ($type !== null) {
            $query->where('type', $type->value);
        }

        /** @var Collection<int, Banner> $banners */
        $banners = $query->get();

        return $banners;
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /** @param array{file: string, type: AccountTypeEnum|string} $data */
    public function createRecord(array $data): Model
    {
        $record = $this->getModel()->newInstance();
        $record->fill($data);
        $record->save();

        return $record;
    }

    /** @param array{file?: string, type?: AccountTypeEnum|string} $data */
    public function updateRecord(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $record;
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}

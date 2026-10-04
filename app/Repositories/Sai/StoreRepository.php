<?php

namespace App\Repositories\Sai;

use App\Helpers\ImageHelper;
use App\Models\Sai\Store;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StoreRepository extends MainRepository
{
    public function __construct(Store $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createRecord(array $data): Model
    {
        $record = $this->getModel()->newInstance();
        $record->fill($data);
        $record->save();

        return $record;
    }

    /** @param array<string, mixed> $data */
    public function updateRecord(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $record;
    }

    /** @param array<string, mixed> $data */
    public function updateOrCreateForOwner(int $ownerId, array $data): Model
    {
        $record = $this->query()->firstOrNew(['owner_id' => $ownerId]);

        if (array_key_exists('cover_image', $data)) {
            $data['cover_image'] = ImageHelper::upload($data['cover_image'], 'stores/covers', $record->cover_image);
        }

        $record->fill(['owner_id' => $ownerId, ...$data]);
        $record->save();

        return $this->query()->with('owner')->findOrFail($record->getKey());
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}

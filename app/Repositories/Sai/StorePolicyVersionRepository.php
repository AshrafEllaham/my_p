<?php

namespace App\Repositories\Sai;

use App\Models\Sai\StorePolicyVersion;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StorePolicyVersionRepository extends MainRepository
{
    public function __construct(StorePolicyVersion $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function currentForStore(int $storeId): ?Model
    {
        return $this->query()
            ->where('store_id', $storeId)
            ->where('is_current', true)
            ->select(['id', 'store_id', 'pickup_hold_hours', 'return_window_days', 'returns_enabled', 'exchanges_enabled', 'cancellation_enabled', 'pickup_instructions', 'return_terms', 'exchange_terms', 'cancellation_terms'])
            ->orderByDesc('version')
            ->first();
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

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}

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

    /** @return array{id: int, pickup_hold_hours: int, return_window_days: int, returns_enabled: bool, exchanges_enabled: bool, cancellation_enabled: bool, pickup_instructions: string|null, return_terms: string|null, exchange_terms: string|null, cancellation_terms: string|null}|null */
    public function currentSnapshotForStore(int $storeId): ?array
    {
        $policy = $this->currentForStore($storeId);

        if ($policy === null) {
            return null;
        }

        return [
            'id' => (int) $policy->getKey(),
            'pickup_hold_hours' => (int) $policy->pickup_hold_hours,
            'return_window_days' => (int) $policy->return_window_days,
            'returns_enabled' => (bool) $policy->returns_enabled,
            'exchanges_enabled' => (bool) $policy->exchanges_enabled,
            'cancellation_enabled' => (bool) $policy->cancellation_enabled,
            'pickup_instructions' => $policy->pickup_instructions,
            'return_terms' => $policy->return_terms,
            'exchange_terms' => $policy->exchange_terms,
            'cancellation_terms' => $policy->cancellation_terms,
        ];
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

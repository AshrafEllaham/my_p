<?php

namespace App\Repositories\Sai;

use App\Models\Sai\ReturnRequest;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReturnRequestRepository extends MainRepository
{
    public function __construct(ReturnRequest $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function existsForOrder(int $orderId): bool
    {
        return $this->query()->where('order_id', $orderId)->exists();
    }

    /** @param array<string, mixed> $data */
    public function createForUserOrder(array $data): int
    {
        return (int) $this->store($data)->getKey();
    }

    public function findForUserOrder(int $returnRequestId, int $userId): Model
    {
        return $this->query()
            ->whereKey($returnRequestId)
            ->where('user_id', $userId)
            ->with(['media:id,return_request_id,path,mime_type,sort_order'])
            ->firstOrFail();
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

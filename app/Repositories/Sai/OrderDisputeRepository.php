<?php

namespace App\Repositories\Sai;

use App\Models\Sai\OrderDispute;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderDisputeRepository extends MainRepository
{
    public function __construct(OrderDispute $model)
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

    public function findForUserOrder(int $orderId, int $userId): OrderDispute
    {
        /** @var OrderDispute $dispute */
        $dispute = $this->query()
            ->where('order_id', $orderId)
            ->whereHas('order', fn (Builder $query) => $query->where('user_id', $userId))
            ->with('messages')
            ->firstOrFail();

        return $dispute;
    }

    public function existsForOrder(int $orderId): bool
    {
        return $this->query()->where('order_id', $orderId)->exists();
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

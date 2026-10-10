<?php

namespace App\Repositories\Sai;

use App\Models\Sai\Wallet;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WalletRepository extends MainRepository
{
    public function __construct(Wallet $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function activeForOwner(int $ownerId): ?Wallet
    {
        return $this->query()
            ->select(['id', 'user_id', 'currency', 'available_balance'])
            ->where('user_id', $ownerId)
            ->where('is_active', true)
            ->first();
    }

    public function activeForOwnerForUpdate(int $ownerId): ?Wallet
    {
        return $this->query()
            ->where('user_id', $ownerId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();
    }

    /** @return array{before: string, after: string} */
    public function updateAvailableBalance(Wallet $wallet, string $balance): array
    {
        $before = (string) $wallet->available_balance;

        $wallet->available_balance = $balance;
        $wallet->save();

        return ['before' => $before, 'after' => $balance];
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

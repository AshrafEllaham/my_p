<?php

namespace App\Repositories\Sai;

use App\Models\Sai\User;
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
            ->select(['id', 'user_id', 'currency', 'available_balance', 'pending_balance'])
            ->where('user_id', $ownerId)
            ->where('is_active', true)
            ->first();
    }

    /** @return array{id: int, available_balance: string, pending_balance: string, currency: string}|null */
    public function activeSnapshotForOwner(int $ownerId): ?array
    {
        $wallet = $this->query()
            ->select(['id', 'available_balance', 'pending_balance', 'currency'])
            ->where('user_id', $ownerId)
            ->where('is_active', true)
            ->first();

        return $wallet === null ? null : $this->walletSnapshot($wallet);
    }

    /** @return array{id: int, available_balance: string, pending_balance: string, currency: string} */
    public function activeSnapshotForOwnerOrCreateForUpdate(int $ownerId): array
    {
        $wallet = $this->query()
            ->where('user_id', $ownerId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if ($wallet === null) {
            $wallet = $this->createRecord([
                'user_id' => $ownerId,
                'is_active' => true,
            ]);
            $wallet->refresh();
        }

        return $this->walletSnapshot($wallet);
    }

    /** @param array<int, int> $ownerIds
     * @return array<int, array{id: int, available_balance: string, pending_balance: string, currency: string}>
     */
    public function activeSnapshotsForOwnersOrCreateForUpdate(array $ownerIds): array
    {
        $ownerIds = array_values(array_unique(array_map('intval', $ownerIds)));
        sort($ownerIds, SORT_NUMERIC);

        $wallets = $this->query()
            ->select(['id', 'user_id', 'currency', 'available_balance', 'pending_balance'])
            ->whereIn('user_id', $ownerIds)
            ->where('is_active', true)
            ->orderBy('user_id')
            ->lockForUpdate()
            ->get();
        $existingOwnerIds = $wallets->pluck('user_id')->map(static fn (int|string $id): int => (int) $id)->all();
        $missingOwnerIds = array_values(array_diff($ownerIds, $existingOwnerIds));

        if ($missingOwnerIds !== []) {
            $timestamp = now();
            $this->getModel()->newQuery()->insert(array_map(
                static fn (int $ownerId): array => [
                    'user_id' => $ownerId,
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                $missingOwnerIds,
            ));

            $wallets = $this->query()
                ->select(['id', 'user_id', 'currency', 'available_balance', 'pending_balance'])
                ->whereIn('user_id', $ownerIds)
                ->where('is_active', true)
                ->orderBy('user_id')
                ->lockForUpdate()
                ->get();
        }

        return $wallets->mapWithKeys(fn (Wallet $wallet): array => [
            (int) $wallet->user_id => $this->walletSnapshot($wallet),
        ])->all();
    }

    public function lockOwnerForUpdate(int $ownerId): void
    {
        // Locking the account serializes idempotency checks and first-time wallet creation.
        User::query()->select('id')->whereKey($ownerId)->lockForUpdate()->firstOrFail();
    }

    public function findForUpdateOrFail(int|string $id): Wallet
    {
        return $this->query()->lockForUpdate()->findOrFail($id);
    }

    /** @return array{id: int, available_balance: string, pending_balance: string, currency: string} */
    public function snapshotForUpdate(int|string $id): array
    {
        return $this->walletSnapshot($this->findForUpdateOrFail($id));
    }

    /** @return array{id: int, available_balance: string, pending_balance: string, currency: string}|null */
    public function snapshotForOwnerByIdForUpdate(int $walletId, int $ownerId): ?array
    {
        $wallet = $this->query()
            ->select(['id', 'user_id', 'currency', 'available_balance', 'pending_balance'])
            ->whereKey($walletId)
            ->where('user_id', $ownerId)
            ->lockForUpdate()
            ->first();

        return $wallet === null ? null : $this->walletSnapshot($wallet);
    }

    public function updateBalancesById(int|string $id, string $availableBalance, string $pendingBalance): void
    {
        $wallet = $this->findForUpdateOrFail($id);
        $wallet->available_balance = $availableBalance;
        $wallet->pending_balance = $pendingBalance;
        $wallet->save();
    }

    public function activeForOwnerForUpdate(int $ownerId): ?Wallet
    {
        return $this->query()
            ->select(['id', 'user_id', 'currency', 'available_balance', 'pending_balance'])
            ->where('user_id', $ownerId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();
    }

    /** @return array{id: int, available_balance: string, pending_balance: string, currency: string}|null */
    public function activeSnapshotForOwnerForUpdate(int $ownerId): ?array
    {
        $wallet = $this->activeForOwnerForUpdate($ownerId);

        return $wallet === null ? null : $this->walletSnapshot($wallet);
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

    /** @return array{id: int, available_balance: string, pending_balance: string, currency: string} */
    private function walletSnapshot(Wallet $wallet): array
    {
        return [
            'id' => (int) $wallet->getKey(),
            'available_balance' => (string) $wallet->available_balance,
            'pending_balance' => (string) $wallet->pending_balance,
            'currency' => (string) $wallet->currency,
        ];
    }
}

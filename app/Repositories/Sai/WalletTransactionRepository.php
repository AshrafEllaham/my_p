<?php

namespace App\Repositories\Sai;

use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Models\Sai\Order;
use App\Models\Sai\WalletTransaction;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WalletTransactionRepository extends MainRepository
{
    public function __construct(WalletTransaction $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function listForOwner(int $ownerId): Builder
    {
        return $this->query()
            ->select([
                'id',
                'public_id',
                'wallet_id',
                'type',
                'status',
                'amount',
                'balance_before',
                'balance_after',
                'currency',
                'completed_at',
                'created_at',
            ])
            ->whereHas('wallet', fn (Builder $query): Builder => $query->where('user_id', $ownerId))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function findForUpdateOrFail(int|string $id): WalletTransaction
    {
        return $this->query()->lockForUpdate()->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createTopUpRecord(array $data): int
    {
        return (int) $this->createRecord($data)->getKey();
    }

    /** @param array<string, mixed> $data */
    public function createWithdrawalRecord(array $data): int
    {
        return (int) $this->createRecord($data)->getKey();
    }

    /** @param array<string, mixed> $data */
    public function createTransferRecord(array $data): void
    {
        $this->createRecord($data);
    }

    /** @param array<string, mixed> $data */
    public function createOrderPurchase(array $data, int $orderId): int
    {
        $data['reference_type'] = Order::class;
        $data['reference_id'] = $orderId;

        return (int) $this->store($data)->getKey();
    }

    /** @param array<string, mixed> $data */
    public function createOrderRefund(array $data, int $orderId): int
    {
        $data['reference_type'] = Order::class;
        $data['reference_id'] = $orderId;

        return (int) $this->store($data)->getKey();
    }

    /** @return array{id: int, wallet_id: int, amount: string, currency: string}|null */
    public function orderPurchaseSnapshotForUpdate(int $transactionId, int $orderId, int $userId): ?array
    {
        $transaction = $this->query()
            ->select(['id', 'wallet_id', 'amount', 'currency'])
            ->whereKey($transactionId)
            ->where('reference_type', Order::class)
            ->where('reference_id', $orderId)
            ->where('type', WalletTransactionTypeEnum::Purchase)
            ->where('status', WalletTransactionStatusEnum::Completed)
            ->whereHas('wallet', fn (Builder $query): Builder => $query->where('user_id', $userId))
            ->lockForUpdate()
            ->first();

        return $transaction === null ? null : [
            'id' => (int) $transaction->getKey(),
            'wallet_id' => (int) $transaction->wallet_id,
            'amount' => (string) $transaction->amount,
            'currency' => (string) $transaction->currency,
        ];
    }

    /** @return array{id: int, public_id: string, wallet_id: int, counterparty_wallet_id: int|null, counterparty_owner_id: int|null, type: WalletTransactionTypeEnum, status: WalletTransactionStatusEnum, amount: string, balance_before: string, balance_after: string, currency: string, completed_at: string|null}|null */
    public function transferSnapshotByIdempotencyKeyForUpdate(string $idempotencyKey): ?array
    {
        $transaction = $this->query()
            ->with(['counterpartyWallet' => fn ($query) => $query->select(['id', 'user_id'])])
            ->where('idempotency_key', $idempotencyKey)
            ->lockForUpdate()
            ->first();

        if ($transaction === null) {
            return null;
        }

        return [
            'id' => (int) $transaction->getKey(),
            'public_id' => (string) $transaction->public_id,
            'wallet_id' => (int) $transaction->wallet_id,
            'counterparty_wallet_id' => $transaction->counterparty_wallet_id === null
                ? null
                : (int) $transaction->counterparty_wallet_id,
            'counterparty_owner_id' => $transaction->counterpartyWallet === null
                ? null
                : (int) $transaction->counterpartyWallet->user_id,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'amount' => (string) $transaction->amount,
            'balance_before' => (string) $transaction->balance_before,
            'balance_after' => (string) $transaction->balance_after,
            'currency' => (string) $transaction->currency,
            'completed_at' => $transaction->completed_at?->toIso8601String(),
        ];
    }

    /** @param array<string, mixed> $data */
    public function updateWithdrawalRecordById(int|string $id, array $data): void
    {
        $transaction = $this->findForUpdateOrFail($id);
        $transaction->fill($data);
        $transaction->save();
    }

    /** @return array{id: int, wallet_id: int, type: WalletTransactionTypeEnum, status: WalletTransactionStatusEnum, amount: string, currency: string} */
    public function topUpSnapshotForUpdate(int|string $id): array
    {
        $transaction = $this->findForUpdateOrFail($id);

        return [
            'id' => (int) $transaction->getKey(),
            'wallet_id' => (int) $transaction->wallet_id,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'amount' => (string) $transaction->amount,
            'currency' => (string) $transaction->currency,
        ];
    }

    /** @return array{id: int, wallet_id: int, type: WalletTransactionTypeEnum, status: WalletTransactionStatusEnum, amount: string, currency: string} */
    public function withdrawalSnapshotForUpdate(int|string $id): array
    {
        $transaction = $this->findForUpdateOrFail($id);

        return [
            'id' => (int) $transaction->getKey(),
            'wallet_id' => (int) $transaction->wallet_id,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'amount' => (string) $transaction->amount,
            'currency' => (string) $transaction->currency,
        ];
    }

    /** @param array<string, mixed> $data */
    public function updateTopUpRecordById(int|string $id, array $data): void
    {
        $transaction = $this->findForUpdateOrFail($id);
        $transaction->fill($data);
        $transaction->save();
    }

    /** @param array<string, mixed> $data */
    public function updateTransaction(WalletTransaction $transaction, array $data): WalletTransaction
    {
        $transaction->fill($data);
        $transaction->save();

        return $transaction;
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
    public function createAdPayment(array $data, Model $ad): Model
    {
        return $this->createRecord($data + [
            'public_id' => (string) Str::uuid(),
            'reference_type' => $ad->getMorphClass(),
            'reference_id' => $ad->getKey(),
        ]);
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

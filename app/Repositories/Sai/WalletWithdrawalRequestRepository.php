<?php

namespace App\Repositories\Sai;

use App\Enums\RequestStatusEnum;
use App\Models\Sai\WalletWithdrawalRequest;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WalletWithdrawalRequestRepository extends MainRepository
{
    public function __construct(WalletWithdrawalRequest $model)
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

    /** @return array<string, mixed>|null */
    public function findByIdempotencyKeyForUpdate(string $key): ?array
    {
        $request = $this->query()->where('idempotency_key', $key)->lockForUpdate()->first();

        return $request === null ? null : $this->requestSnapshot($request);
    }

    public function hasPendingForWallet(int $walletId): bool
    {
        return $this->query()
            ->where('wallet_id', $walletId)
            ->where('status', RequestStatusEnum::Pending)
            ->exists();
    }

    public function hasPendingForOwner(int $ownerId): bool
    {
        return $this->query()
            ->where('status', RequestStatusEnum::Pending)
            ->whereHas('wallet', fn (Builder $query): Builder => $query->where('user_id', $ownerId))
            ->exists();
    }

    /** @param array<string, mixed> $data */
    public function createWithdrawalRequest(array $data): int
    {
        return (int) $this->createRecord($data)->getKey();
    }

    public function updateWalletTransactionId(int $id, int $transactionId): void
    {
        $request = $this->query()->lockForUpdate()->findOrFail($id);
        $request->wallet_transaction_id = $transactionId;
        $request->save();
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

    /** @return array<string, mixed> */
    private function requestSnapshot(WalletWithdrawalRequest $request): array
    {
        return [
            'id' => (int) $request->getKey(),
            'public_id' => (string) $request->public_id,
            'wallet_id' => (int) $request->wallet_id,
            'method' => $request->method,
            'destination' => (string) $request->destination,
            'amount' => (string) $request->amount,
            'currency' => (string) $request->currency,
            'status' => $request->status,
            'review_note' => $request->review_note,
            'created_at' => $request->created_at?->toIso8601String(),
        ];
    }
}

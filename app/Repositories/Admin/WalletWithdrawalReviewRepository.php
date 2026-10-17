<?php

namespace App\Repositories\Admin;

use App\Enums\RequestStatusEnum;
use App\Models\Sai\WalletWithdrawalRequest;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class WalletWithdrawalReviewRepository extends MainRepository
{
    public function __construct(WalletWithdrawalRequest $model)
    {
        $this->model = $model;
    }

    public function pendingQuery(): Builder
    {
        return $this->getModel()->newQuery()
            ->join('wallets', 'wallets.id', '=', 'wallet_withdrawal_requests.wallet_id')
            ->join('users', 'users.id', '=', 'wallets.user_id')
            ->where('wallet_withdrawal_requests.status', RequestStatusEnum::Pending)
            ->select([
                'wallet_withdrawal_requests.id',
                'wallet_withdrawal_requests.method',
                'wallet_withdrawal_requests.destination',
                'wallet_withdrawal_requests.amount',
                'wallet_withdrawal_requests.currency',
                'wallet_withdrawal_requests.created_at',
                'users.name as owner_name',
            ])
            ->orderByDesc('wallet_withdrawal_requests.created_at')
            ->orderByDesc('wallet_withdrawal_requests.id');
    }

    public function pendingCount(): int
    {
        return $this->getModel()->newQuery()
            ->where('status', RequestStatusEnum::Pending)
            ->count();
    }

    public function findPendingForReview(int $id): WalletWithdrawalRequest
    {
        /** @var WalletWithdrawalRequest $withdrawalRequest */
        $withdrawalRequest = $this->getModel()->newQuery()
            ->select([
                'id',
                'public_id',
                'wallet_id',
                'wallet_transaction_id',
                'method',
                'destination',
                'amount',
                'currency',
                'status',
                'review_note',
                'processed_at',
                'created_at',
                'updated_at',
            ])
            ->with(['wallet:id,user_id', 'wallet.user:id,name'])
            ->where('status', RequestStatusEnum::Pending)
            ->findOrFail($id);

        return $withdrawalRequest;
    }

    public function lockPendingForReview(int $id): WalletWithdrawalRequest
    {
        /** @var WalletWithdrawalRequest $withdrawalRequest */
        $withdrawalRequest = $this->getModel()->newQuery()
            ->select([
                'id',
                'public_id',
                'wallet_id',
                'wallet_transaction_id',
                'method',
                'destination',
                'amount',
                'currency',
                'status',
                'review_note',
                'processed_at',
                'created_at',
                'updated_at',
            ])
            ->with('walletTransaction')
            ->where('status', RequestStatusEnum::Pending)
            ->lockForUpdate()
            ->findOrFail($id);

        return $withdrawalRequest;
    }

    /** @param array<string, mixed> $data */
    public function updateReview(WalletWithdrawalRequest $withdrawalRequest, array $data): WalletWithdrawalRequest
    {
        $withdrawalRequest->fill($data);
        $withdrawalRequest->save();

        return $withdrawalRequest->refresh();
    }

    public function updateWalletTransactionId(WalletWithdrawalRequest $withdrawalRequest, int $transactionId): void
    {
        $withdrawalRequest->wallet_transaction_id = $transactionId;
        $withdrawalRequest->save();
    }
}

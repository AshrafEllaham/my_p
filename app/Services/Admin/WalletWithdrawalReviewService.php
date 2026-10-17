<?php

namespace App\Services\Admin;

use App\Enums\RequestStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Models\Sai\WalletWithdrawalRequest;
use App\Repositories\Admin\WalletWithdrawalReviewRepository;
use App\Repositories\Sai\WalletRepository;
use App\Repositories\Sai\WalletTransactionRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletWithdrawalReviewService
{
    public function __construct(
        private readonly WalletWithdrawalReviewRepository $repository,
        private readonly WalletRepository $walletRepository,
        private readonly WalletTransactionRepository $transactionRepository,
        private readonly DatabaseManager $database,
    ) {}

    public function pendingQuery(): Builder
    {
        return $this->repository->pendingQuery();
    }

    public function pendingCount(): int
    {
        return $this->repository->pendingCount();
    }

    public function findPending(int $id): WalletWithdrawalRequest
    {
        return $this->repository->findPendingForReview($id);
    }

    public function approve(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $pendingRequest = $this->repository->findPendingForReview($id);
            $wallet = $this->walletRepository->snapshotForUpdate($pendingRequest->wallet_id);
            $withdrawalRequest = $this->repository->lockPendingForReview($id);

            if ((int) $withdrawalRequest->wallet_id !== $wallet['id']) {
                throw ValidationException::withMessages([
                    'withdrawal' => [__('admin.wallet_withdrawals.messages.unavailable')],
                ]);
            }

            if ($wallet['currency'] !== (string) $withdrawalRequest->currency) {
                throw ValidationException::withMessages([
                    'withdrawal' => [__('admin.wallet_withdrawals.messages.unavailable')],
                ]);
            }

            $amountInCents = $this->moneyToCents((string) $withdrawalRequest->amount);
            $balanceBefore = $this->moneyToCents($wallet['available_balance']);

            if ($balanceBefore < $amountInCents) {
                throw ValidationException::withMessages([
                    'withdrawal' => [__('admin.wallet_withdrawals.messages.insufficient_balance')],
                ]);
            }

            $balanceAfter = $balanceBefore - $amountInCents;
            $this->walletRepository->updateBalancesById(
                $wallet['id'],
                $this->centsToMoney($balanceAfter),
                $wallet['pending_balance'],
            );

            $transactionData = [
                'status' => WalletTransactionStatusEnum::Completed,
                'balance_before' => $this->centsToMoney($balanceBefore),
                'balance_after' => $this->centsToMoney($balanceAfter),
                'completed_at' => now(),
            ];

            if ($withdrawalRequest->wallet_transaction_id !== null) {
                $transaction = $this->transactionRepository->withdrawalSnapshotForUpdate($withdrawalRequest->wallet_transaction_id);
                $this->ensureLinkedTransactionIsPending($withdrawalRequest, $transaction);
                $this->transactionRepository->updateWithdrawalRecordById($transaction['id'], $transactionData);
            } else {
                $transactionId = $this->transactionRepository->createWithdrawalRecord([
                    'public_id' => (string) Str::uuid(),
                    'wallet_id' => $wallet['id'],
                    'type' => WalletTransactionTypeEnum::Withdrawal,
                    ...$transactionData,
                    'amount' => (string) $withdrawalRequest->amount,
                    'balance_before' => $this->centsToMoney($balanceBefore),
                    'balance_after' => $this->centsToMoney($balanceAfter),
                    'currency' => (string) $withdrawalRequest->currency,
                    'idempotency_key' => 'wallet-withdrawal-approved-'.$withdrawalRequest->getKey().'-'.Str::uuid(),
                    'metadata' => ['withdrawal_request_id' => (int) $withdrawalRequest->getKey()],
                ]);
                $this->repository->updateWalletTransactionId($withdrawalRequest, $transactionId);
            }

            $this->repository->updateReview($withdrawalRequest, [
                'status' => RequestStatusEnum::Completed,
                'review_note' => null,
                'processed_at' => now(),
            ]);
        });
    }

    public function reject(int $id, string $reason): void
    {
        $this->database->transaction(function () use ($id, $reason): void {
            $pendingRequest = $this->repository->findPendingForReview($id);
            $this->walletRepository->snapshotForUpdate($pendingRequest->wallet_id);
            $withdrawalRequest = $this->repository->lockPendingForReview($id);

            if ($withdrawalRequest->wallet_transaction_id !== null) {
                $transaction = $this->transactionRepository->withdrawalSnapshotForUpdate($withdrawalRequest->wallet_transaction_id);
                $this->ensureLinkedTransactionIsPending($withdrawalRequest, $transaction);
                $metadata = (array) ($withdrawalRequest->walletTransaction?->metadata ?? []);
                $metadata['rejection_reason'] = trim($reason);

                $this->transactionRepository->updateWithdrawalRecordById($transaction['id'], [
                    'status' => WalletTransactionStatusEnum::Failed,
                    'metadata' => $metadata,
                ]);
            }

            $this->repository->updateReview($withdrawalRequest, [
                'status' => RequestStatusEnum::Rejected,
                'review_note' => trim($reason),
                'processed_at' => now(),
            ]);
        });
    }

    /** @param array{id: int, wallet_id: int, type: WalletTransactionTypeEnum, status: WalletTransactionStatusEnum, amount: string, currency: string} $transaction */
    private function ensureLinkedTransactionIsPending(WalletWithdrawalRequest $withdrawalRequest, array $transaction): void
    {
        if (
            $transaction['type'] !== WalletTransactionTypeEnum::Withdrawal
            || $transaction['status'] !== WalletTransactionStatusEnum::Pending
            || $transaction['wallet_id'] !== (int) $withdrawalRequest->wallet_id
            || $transaction['currency'] !== (string) $withdrawalRequest->currency
            || $this->moneyToCents($transaction['amount']) !== $this->moneyToCents((string) $withdrawalRequest->amount)
        ) {
            throw ValidationException::withMessages([
                'withdrawal' => [__('admin.wallet_withdrawals.messages.unavailable')],
            ]);
        }
    }

    private function moneyToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function centsToMoney(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}

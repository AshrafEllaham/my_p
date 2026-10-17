<?php

namespace App\Services\Sai;

use App\Enums\PayoutMethodEnum;
use App\Enums\RequestStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Repositories\Sai\WalletRepository;
use App\Repositories\Sai\WalletTransactionRepository;
use App\Repositories\Sai\WalletWithdrawalRequestRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletWithdrawalRequestService
{
    private const MAX_WALLET_BALANCE_IN_CENTS = 99_999_999_999_999;

    public function __construct(
        private readonly WalletWithdrawalRequestRepository $repository,
        private readonly WalletRepository $walletRepository,
        private readonly WalletTransactionRepository $transactionRepository,
        private readonly DatabaseManager $database,
    ) {}

    public function hasPendingForOwner(int $ownerId): bool
    {
        return $this->repository->hasPendingForOwner($ownerId);
    }

    /**
     * @param  array{amount: string|int|float, method: string, destination: string, idempotency_key: string}  $data
     * @return array<string, mixed>
     */
    public function requestWithdrawal(int $ownerId, array $data): array
    {
        $amountInCents = $this->moneyToCents((string) $data['amount']);
        $method = PayoutMethodEnum::from($data['method']);
        $destination = trim($data['destination']);
        $idempotencyKey = "wallet-withdrawal-{$ownerId}-{$data['idempotency_key']}";

        return $this->database->transaction(function () use ($ownerId, $amountInCents, $method, $destination, $idempotencyKey): array {
            $this->walletRepository->lockOwnerForUpdate($ownerId);

            $existing = $this->repository->findByIdempotencyKeyForUpdate($idempotencyKey);
            if ($existing !== null) {
                if (
                    $this->moneyToCents($existing['amount']) !== $amountInCents
                    || $existing['method'] !== $method
                    || $existing['destination'] !== $destination
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => [__('messages.wallet.withdrawal_idempotency_conflict')],
                    ]);
                }

                return $this->resourceData($existing);
            }

            $wallet = $this->walletRepository->activeSnapshotForOwnerOrCreateForUpdate($ownerId);
            if ($this->repository->hasPendingForWallet($wallet['id'])) {
                throw ValidationException::withMessages([
                    'withdrawal' => [__('messages.wallet.pending_withdrawal_exists')],
                ]);
            }

            $balanceInCents = $this->moneyToCents($wallet['available_balance']);
            if ($amountInCents > $balanceInCents) {
                throw ValidationException::withMessages([
                    'amount' => [__('messages.wallet.withdrawal_amount_exceeds_balance')],
                ]);
            }

            if ($amountInCents > self::MAX_WALLET_BALANCE_IN_CENTS) {
                throw ValidationException::withMessages([
                    'amount' => [__('messages.validation.wallet_withdrawal.amount.max')],
                ]);
            }

            $publicId = (string) Str::uuid();
            $createdAt = now();
            $requestId = $this->repository->createWithdrawalRequest([
                'public_id' => $publicId,
                'wallet_id' => $wallet['id'],
                'method' => $method,
                'destination' => $destination,
                'amount' => $this->centsToMoney($amountInCents),
                'currency' => $wallet['currency'],
                'status' => RequestStatusEnum::Pending,
                'idempotency_key' => $idempotencyKey,
            ]);

            $transactionId = $this->transactionRepository->createWithdrawalRecord([
                'public_id' => (string) Str::uuid(),
                'wallet_id' => $wallet['id'],
                'type' => WalletTransactionTypeEnum::Withdrawal,
                'status' => WalletTransactionStatusEnum::Pending,
                'amount' => $this->centsToMoney($amountInCents),
                'balance_before' => $wallet['available_balance'],
                'balance_after' => $wallet['available_balance'],
                'currency' => $wallet['currency'],
                'idempotency_key' => "{$idempotencyKey}-transaction",
                'metadata' => ['withdrawal_request_id' => $requestId],
            ]);

            $this->repository->updateWalletTransactionId($requestId, $transactionId);

            return [
                'id' => $publicId,
                'method' => $method->value,
                'destination' => $destination,
                'amount' => $this->centsToMoney($amountInCents),
                'currency' => $wallet['currency'],
                'status' => RequestStatusEnum::Pending->value,
                'created_at' => $createdAt->toIso8601String(),
            ];
        });
    }

    /** @param array<string, mixed> $request */
    private function resourceData(array $request): array
    {
        return [
            'id' => $request['public_id'],
            'method' => $request['method']->value,
            'destination' => $request['destination'],
            'amount' => $request['amount'],
            'currency' => $request['currency'],
            'status' => $request['status']->value,
            'review_note' => $request['review_note'],
            'created_at' => $request['created_at'],
        ];
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

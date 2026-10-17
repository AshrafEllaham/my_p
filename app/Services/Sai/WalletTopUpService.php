<?php

namespace App\Services\Sai;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentOperationTableEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Repositories\Sai\PaymentRepository;
use App\Repositories\Sai\WalletRepository;
use App\Repositories\Sai\WalletTransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletTopUpService
{
    private const MAX_WALLET_BALANCE_IN_CENTS = 99_999_999_999_999;

    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly WalletTransactionRepository $transactionRepository,
        private readonly PaymentRepository $paymentRepository,
    ) {}

    /** @return array{balance: string, pending_balance: string, currency: string} */
    public function balanceForOwner(int $ownerId): array
    {
        $wallet = $this->walletRepository->activeSnapshotForOwner($ownerId);

        return [
            'balance' => $wallet['available_balance'] ?? '0.00',
            'pending_balance' => $wallet['pending_balance'] ?? '0.00',
            'currency' => $wallet['currency'] ?? 'EGP',
        ];
    }

    /**
     * @param  array{amount: string|int|float, idempotency_key: string}  $data
     * @return array{id: int, status: PaymentStatusEnum, method: PaymentMethodEnum, amount: string, currency: string, wallet_transaction_id: int|null}
     */
    public function requestTopUp(int $ownerId, array $data): array
    {
        $amountInCents = $this->moneyToCents((string) $data['amount']);
        $idempotencyKey = "wallet-top-up-{$ownerId}-{$data['idempotency_key']}";

        return DB::transaction(function () use ($ownerId, $amountInCents, $idempotencyKey): array {
            $this->walletRepository->lockOwnerForUpdate($ownerId);
            $existingPayment = $this->paymentRepository->topUpByIdempotencyKeyForUpdate($idempotencyKey);

            if ($existingPayment !== null) {
                if ($this->moneyToCents($existingPayment['amount']) !== $amountInCents) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => [__('messages.wallet.idempotency_conflict')],
                    ]);
                }

                return $existingPayment;
            }

            $wallet = $this->walletRepository->activeSnapshotForOwnerOrCreateForUpdate($ownerId);
            $amount = $this->centsToMoney($amountInCents);
            $availableBefore = $this->moneyToCents($wallet['available_balance']);
            $pendingBefore = $this->moneyToCents($wallet['pending_balance']);

            if ($availableBefore + $pendingBefore + $amountInCents > self::MAX_WALLET_BALANCE_IN_CENTS) {
                throw ValidationException::withMessages([
                    'amount' => [__('messages.validation.wallet_top_up.amount.max')],
                ]);
            }

            $this->walletRepository->updateBalancesById(
                $wallet['id'],
                $wallet['available_balance'],
                $this->centsToMoney($pendingBefore + $amountInCents),
            );

            $transactionId = $this->transactionRepository->createTopUpRecord([
                'public_id' => (string) Str::uuid(),
                'wallet_id' => $wallet['id'],
                'type' => WalletTransactionTypeEnum::Deposit,
                'status' => WalletTransactionStatusEnum::Pending,
                'amount' => $amount,
                'balance_before' => $wallet['available_balance'],
                'balance_after' => $wallet['available_balance'],
                'currency' => $wallet['currency'],
                'idempotency_key' => $idempotencyKey,
            ]);

            return $this->paymentRepository->createTopUpRecord([
                'public_id' => (string) Str::uuid(),
                'wallet_transaction_id' => $transactionId,
                'status' => PaymentStatusEnum::Pending,
                'method' => PaymentMethodEnum::Online,
                'amount' => $amount,
                'currency' => $wallet['currency'],
                'idempotency_key' => $idempotencyKey,
            ]);
        });
    }

    public function completeOnlineTopUp(int|string $paymentId, PaymentOperationTableEnum $operation): PaymentStatusEnum
    {
        return DB::transaction(function () use ($paymentId, $operation): PaymentStatusEnum {
            $payment = $this->paymentRepository->topUpSnapshotForUpdate($paymentId);
            $transaction = $this->pendingTopUpTransaction($payment, $operation);

            if ($payment['status'] !== PaymentStatusEnum::Pending) {
                return $payment['status'];
            }

            $wallet = $this->walletRepository->snapshotForUpdate($transaction['wallet_id']);
            $balanceBefore = $this->moneyToCents($wallet['available_balance']);
            $pendingBefore = $this->moneyToCents($wallet['pending_balance']);
            $amount = $this->moneyToCents($transaction['amount']);

            if ($transaction['status'] !== WalletTransactionStatusEnum::Pending || $pendingBefore < $amount) {
                throw ValidationException::withMessages([
                    'payment' => [__('messages.wallet.payment_unavailable')],
                ]);
            }

            $this->walletRepository->updateBalancesById(
                $wallet['id'],
                $this->centsToMoney($balanceBefore + $amount),
                $this->centsToMoney($pendingBefore - $amount),
            );
            $this->transactionRepository->updateTopUpRecordById($transaction['id'], [
                'status' => WalletTransactionStatusEnum::Completed,
                'balance_before' => $this->centsToMoney($balanceBefore),
                'balance_after' => $this->centsToMoney($balanceBefore + $amount),
                'completed_at' => now(),
            ]);
            $this->paymentRepository->updateTopUpRecordById($payment['id'], [
                'status' => PaymentStatusEnum::Paid,
                'paid_at' => now(),
            ]);

            return PaymentStatusEnum::Paid;
        });
    }

    public function failOnlineTopUp(int|string $paymentId, PaymentOperationTableEnum $operation): PaymentStatusEnum
    {
        return DB::transaction(function () use ($paymentId, $operation): PaymentStatusEnum {
            $payment = $this->paymentRepository->topUpSnapshotForUpdate($paymentId);
            $transaction = $this->pendingTopUpTransaction($payment, $operation);

            if ($payment['status'] !== PaymentStatusEnum::Pending) {
                return $payment['status'];
            }

            $wallet = $this->walletRepository->snapshotForUpdate($transaction['wallet_id']);
            $pendingBefore = $this->moneyToCents($wallet['pending_balance']);
            $amount = $this->moneyToCents($transaction['amount']);

            if ($transaction['status'] !== WalletTransactionStatusEnum::Pending || $pendingBefore < $amount) {
                throw ValidationException::withMessages([
                    'payment' => [__('messages.wallet.payment_unavailable')],
                ]);
            }

            $this->walletRepository->updateBalancesById(
                $wallet['id'],
                $wallet['available_balance'],
                $this->centsToMoney($pendingBefore - $amount),
            );
            $this->transactionRepository->updateTopUpRecordById($transaction['id'], [
                'status' => WalletTransactionStatusEnum::Failed,
            ]);
            $this->paymentRepository->updateTopUpRecordById($payment['id'], [
                'status' => PaymentStatusEnum::Failed,
            ]);

            return PaymentStatusEnum::Failed;
        });
    }

    /**
     * @param  array{id: int, status: PaymentStatusEnum, method: PaymentMethodEnum, amount: string, currency: string, wallet_transaction_id: int|null}  $payment
     * @return array{id: int, wallet_id: int, type: WalletTransactionTypeEnum, status: WalletTransactionStatusEnum, amount: string, currency: string}
     */
    private function pendingTopUpTransaction(array $payment, PaymentOperationTableEnum $operation): array
    {
        if (
            $payment['method'] !== PaymentMethodEnum::Online
            || $operation !== PaymentOperationTableEnum::WalletTopUp
            || $payment['wallet_transaction_id'] === null
        ) {
            throw ValidationException::withMessages([
                'payment' => [__('messages.wallet.payment_unavailable')],
            ]);
        }

        $transaction = $this->transactionRepository->topUpSnapshotForUpdate($payment['wallet_transaction_id']);

        if (
            $transaction['type'] !== WalletTransactionTypeEnum::Deposit
            || $transaction['currency'] !== $payment['currency']
            || $this->moneyToCents($transaction['amount']) !== $this->moneyToCents($payment['amount'])
        ) {
            throw ValidationException::withMessages([
                'payment' => [__('messages.wallet.payment_unavailable')],
            ]);
        }

        return $transaction;
    }

    private function moneyToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function centsToMoney(int $amount): string
    {
        return sprintf('%d.%02d', intdiv($amount, 100), $amount % 100);
    }
}

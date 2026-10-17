<?php

namespace App\Services\Sai;

use App\Enums\UserStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Repositories\Sai\UserRepository;
use App\Repositories\Sai\WalletRepository;
use App\Repositories\Sai\WalletTransactionRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletTransferService
{
    private const MAX_WALLET_BALANCE_IN_CENTS = 99_999_999_999_999;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly WalletRepository $walletRepository,
        private readonly WalletTransactionRepository $transactionRepository,
        private readonly DatabaseManager $database,
    ) {}

    /**
     * @param  array{recipient: string, amount: string|int|float, idempotency_key: string}  $data
     * @return array{id: string, recipient_name: string|null, amount: string, currency: string, status: WalletTransactionStatusEnum, balance_before: string, balance_after: string, completed_at: string|null}
     */
    public function transfer(int $senderId, array $data): array
    {
        $amountInCents = $this->moneyToCents((string) $data['amount']);
        $recipient = $this->userRepository->findWalletTransferUserByIdentity($data['recipient']);

        if ($recipient === null) {
            throw ValidationException::withMessages([
                'recipient' => [__('messages.wallet.transfer_recipient_unavailable')],
            ]);
        }

        if ($recipient['id'] === $senderId) {
            throw ValidationException::withMessages([
                'recipient' => [__('messages.wallet.transfer_self_not_allowed')],
            ]);
        }

        $idempotencyKey = "wallet-transfer-{$senderId}-{$data['idempotency_key']}";

        return $this->database->transaction(function () use ($senderId, $recipient, $amountInCents, $idempotencyKey): array {
            $lockedOwnerIds = $this->userRepository->lockOwnersForUpdate([$senderId, $recipient['id']]);
            if (count($lockedOwnerIds) !== 2) {
                throw ValidationException::withMessages([
                    'recipient' => [__('messages.wallet.transfer_recipient_unavailable')],
                ]);
            }

            $sender = $this->userRepository->findWalletTransferUserById($senderId);
            $lockedRecipient = $this->userRepository->findWalletTransferUserById($recipient['id']);
            if ($sender === null || $sender['status'] !== UserStatusEnum::Active) {
                throw ValidationException::withMessages([
                    'transfer' => [__('messages.wallet.transfer_unavailable')],
                ]);
            }

            if ($lockedRecipient === null) {
                throw ValidationException::withMessages([
                    'recipient' => [__('messages.wallet.transfer_recipient_unavailable')],
                ]);
            }

            $outgoingIdempotencyKey = "{$idempotencyKey}-out";
            $existing = $this->transactionRepository->transferSnapshotByIdempotencyKeyForUpdate($outgoingIdempotencyKey);
            if ($existing !== null) {
                if (
                    $existing['type'] !== WalletTransactionTypeEnum::TransferOut
                    || $existing['status'] !== WalletTransactionStatusEnum::Completed
                    || $existing['counterparty_owner_id'] !== $recipient['id']
                    || $this->moneyToCents($existing['amount']) !== $amountInCents
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => [__('messages.wallet.transfer_idempotency_conflict')],
                    ]);
                }

                return $this->transferResponse($existing, $lockedRecipient['name']);
            }

            if ($lockedRecipient['status'] !== UserStatusEnum::Active) {
                throw ValidationException::withMessages([
                    'recipient' => [__('messages.wallet.transfer_recipient_unavailable')],
                ]);
            }

            $wallets = $this->walletRepository->activeSnapshotsForOwnersOrCreateForUpdate($lockedOwnerIds);
            $senderWallet = $wallets[$senderId] ?? null;
            $recipientWallet = $wallets[$recipient['id']] ?? null;

            if ($senderWallet === null || $recipientWallet === null) {
                throw ValidationException::withMessages([
                    'transfer' => [__('messages.wallet.transfer_unavailable')],
                ]);
            }

            if ($senderWallet['currency'] !== $recipientWallet['currency']) {
                throw ValidationException::withMessages([
                    'recipient' => [__('messages.wallet.transfer_currency_mismatch')],
                ]);
            }

            $senderBalanceInCents = $this->moneyToCents($senderWallet['available_balance']);
            if ($amountInCents > $senderBalanceInCents) {
                throw ValidationException::withMessages([
                    'amount' => [__('messages.wallet.transfer_amount_exceeds_balance')],
                ]);
            }

            $recipientBalanceInCents = $this->moneyToCents($recipientWallet['available_balance']);
            $recipientPendingBalanceInCents = $this->moneyToCents($recipientWallet['pending_balance']);
            if ($recipientBalanceInCents + $recipientPendingBalanceInCents + $amountInCents > self::MAX_WALLET_BALANCE_IN_CENTS) {
                throw ValidationException::withMessages([
                    'amount' => [__('messages.wallet.transfer_recipient_balance_limit')],
                ]);
            }

            $senderBalanceAfter = $senderBalanceInCents - $amountInCents;
            $recipientBalanceAfter = $recipientBalanceInCents + $amountInCents;
            $amount = $this->centsToMoney($amountInCents);
            $completedAt = now();
            $outgoingPublicId = (string) Str::uuid();

            $this->walletRepository->updateBalancesById(
                $senderWallet['id'],
                $this->centsToMoney($senderBalanceAfter),
                $senderWallet['pending_balance'],
            );
            $this->walletRepository->updateBalancesById(
                $recipientWallet['id'],
                $this->centsToMoney($recipientBalanceAfter),
                $recipientWallet['pending_balance'],
            );

            $this->transactionRepository->createTransferRecord([
                'public_id' => $outgoingPublicId,
                'wallet_id' => $senderWallet['id'],
                'counterparty_wallet_id' => $recipientWallet['id'],
                'type' => WalletTransactionTypeEnum::TransferOut,
                'status' => WalletTransactionStatusEnum::Completed,
                'amount' => $amount,
                'balance_before' => $senderWallet['available_balance'],
                'balance_after' => $this->centsToMoney($senderBalanceAfter),
                'currency' => $senderWallet['currency'],
                'idempotency_key' => $outgoingIdempotencyKey,
                'completed_at' => $completedAt,
            ]);
            $this->transactionRepository->createTransferRecord([
                'public_id' => (string) Str::uuid(),
                'wallet_id' => $recipientWallet['id'],
                'counterparty_wallet_id' => $senderWallet['id'],
                'type' => WalletTransactionTypeEnum::TransferIn,
                'status' => WalletTransactionStatusEnum::Completed,
                'amount' => $amount,
                'balance_before' => $recipientWallet['available_balance'],
                'balance_after' => $this->centsToMoney($recipientBalanceAfter),
                'currency' => $recipientWallet['currency'],
                'idempotency_key' => "{$idempotencyKey}-in",
                'completed_at' => $completedAt,
            ]);

            return [
                'id' => $outgoingPublicId,
                'recipient_name' => $lockedRecipient['name'],
                'amount' => $amount,
                'currency' => $senderWallet['currency'],
                'status' => WalletTransactionStatusEnum::Completed,
                'balance_before' => $senderWallet['available_balance'],
                'balance_after' => $this->centsToMoney($senderBalanceAfter),
                'completed_at' => $completedAt->toIso8601String(),
            ];
        });
    }

    /** @param array<string, mixed> $transaction
     * @return array{id: string, recipient_name: string|null, amount: string, currency: string, status: WalletTransactionStatusEnum, balance_before: string, balance_after: string, completed_at: string|null}
     */
    private function transferResponse(array $transaction, ?string $recipientName): array
    {
        return [
            'id' => $transaction['public_id'],
            'recipient_name' => $recipientName,
            'amount' => $transaction['amount'],
            'currency' => $transaction['currency'],
            'status' => $transaction['status'],
            'balance_before' => $transaction['balance_before'],
            'balance_after' => $transaction['balance_after'],
            'completed_at' => $transaction['completed_at'],
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

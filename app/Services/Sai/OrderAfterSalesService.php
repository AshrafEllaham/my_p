<?php

namespace App\Services\Sai;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnResolutionTypeEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Repositories\Sai\OrderRepository;
use App\Repositories\Sai\OrderStatusHistoryRepository;
use App\Repositories\Sai\PaymentRepository;
use App\Repositories\Sai\ProductRepository;
use App\Repositories\Sai\RefundRepository;
use App\Repositories\Sai\ReturnRequestMediaRepository;
use App\Repositories\Sai\ReturnRequestRepository;
use App\Repositories\Sai\ReviewRepository;
use App\Repositories\Sai\WalletRepository;
use App\Repositories\Sai\WalletTransactionRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderAfterSalesService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderStatusHistoryRepository $history,
        private readonly PaymentRepository $payments,
        private readonly ProductRepository $products,
        private readonly RefundRepository $refunds,
        private readonly ReturnRequestRepository $returnRequests,
        private readonly ReturnRequestMediaRepository $returnMedia,
        private readonly ReviewRepository $reviews,
        private readonly WalletRepository $wallets,
        private readonly WalletTransactionRepository $walletTransactions,
        private readonly DatabaseManager $database,
        private readonly FilesystemManager $filesystem,
    ) {}

    public function cancel(int $userId, int $orderId, ?string $reason): Model
    {
        return $this->database->transaction(function () use ($userId, $orderId, $reason): Model {
            $order = $this->orders->lockUserOrderSnapshot($orderId, $userId);
            $status = OrderStatusEnum::from($order['status']);

            if ($status === OrderStatusEnum::Cancelled) {
                return $this->orders->findForUser($orderId, $userId);
            }

            $policy = $order['policy_snapshot'] ?? [];
            if (
                ! (bool) ($policy['cancellation_enabled'] ?? false)
                || ! in_array($status, [OrderStatusEnum::PendingStore, OrderStatusEnum::Confirmed], true)
            ) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.orders.cancellation_unavailable')],
                ]);
            }

            $now = now();
            $orderUpdates = [
                'status' => OrderStatusEnum::Cancelled->value,
                'cancelled_at' => $now,
                'cancellation_reason' => $reason,
            ];

            $item = $order['items'][0] ?? null;
            if ($item !== null && $item['product_id'] !== null) {
                $this->products->restoreStock($item['product_id'], $item['quantity']);
            }

            if (PaymentStatusEnum::from($order['payment_status']) === PaymentStatusEnum::Paid) {
                $payment = $this->payments->orderPaymentForUpdate($orderId);

                if (
                    $payment === null
                    || $payment['status'] !== PaymentStatusEnum::Paid
                    || $payment['method'] !== PaymentMethodEnum::Wallet
                    || $payment['amount'] !== $order['total_amount']
                    || $payment['currency'] !== $order['currency']
                ) {
                    throw ValidationException::withMessages([
                        'order' => [__('messages.orders.refund_unavailable')],
                    ]);
                }

                $amountCents = $this->moneyToCents($order['total_amount']);
                $refundTransactionId = null;
                if ($amountCents > 0) {
                    $purchase = $payment['wallet_transaction_id'] === null
                        ? null
                        : $this->walletTransactions->orderPurchaseSnapshotForUpdate(
                            $payment['wallet_transaction_id'],
                            $orderId,
                            $userId,
                        );
                    $wallet = $purchase === null
                        ? null
                        : $this->wallets->snapshotForOwnerByIdForUpdate($purchase['wallet_id'], $userId);

                    if (
                        $purchase === null
                        || $purchase['amount'] !== $payment['amount']
                        || $purchase['currency'] !== $payment['currency']
                        || $wallet === null
                        || $wallet['currency'] !== $payment['currency']
                    ) {
                        throw ValidationException::withMessages([
                            'order' => [__('messages.orders.refund_unavailable')],
                        ]);
                    }

                    $balanceBeforeCents = $this->moneyToCents($wallet['available_balance']);
                    $balanceAfter = $this->centsToMoney($balanceBeforeCents + $amountCents);
                    $refundTransactionId = $this->walletTransactions->createOrderRefund([
                        'public_id' => (string) Str::uuid(),
                        'wallet_id' => $wallet['id'],
                        'type' => WalletTransactionTypeEnum::Refund,
                        'status' => WalletTransactionStatusEnum::Completed,
                        'amount' => $payment['amount'],
                        'balance_before' => $wallet['available_balance'],
                        'balance_after' => $balanceAfter,
                        'currency' => $wallet['currency'],
                        'idempotency_key' => "order-cancel-refund-{$orderId}",
                        'metadata' => ['order_id' => $orderId],
                        'completed_at' => $now,
                    ], $orderId);

                    $this->wallets->updateBalancesById($wallet['id'], $balanceAfter, $wallet['pending_balance']);
                }

                $this->payments->updateOrderPayment($payment['id'], ['status' => PaymentStatusEnum::Refunded]);
                $this->refunds->store([
                    'public_id' => (string) Str::uuid(),
                    'order_id' => $orderId,
                    'payment_id' => $payment['id'],
                    'wallet_transaction_id' => $refundTransactionId,
                    'status' => WalletTransactionStatusEnum::Completed,
                    'amount' => $payment['amount'],
                    'currency' => $order['currency'],
                    'idempotency_key' => "order-cancel-refund-{$orderId}",
                    'reason' => __('messages.orders.cancellation_refund_reason'),
                    'completed_at' => $now,
                ]);
                $orderUpdates['payment_status'] = PaymentStatusEnum::Refunded->value;
            }

            $this->orders->updateForUserOrder($orderId, $orderUpdates);

            $this->history->store([
                'order_id' => $orderId,
                'from_status' => $status,
                'to_status' => OrderStatusEnum::Cancelled,
                'actor_id' => $userId,
                'note' => $reason,
                'metadata' => null,
            ]);

            return $this->orders->findForUser($orderId, $userId);
        });
    }

    /** @param array{rating: int|string, comment?: string|null} $data */
    public function review(int $userId, int $orderId, array $data): Model
    {
        return $this->database->transaction(function () use ($userId, $orderId, $data): Model {
            $order = $this->orders->lockUserOrderSnapshot($orderId, $userId);
            if (OrderStatusEnum::from($order['status']) !== OrderStatusEnum::Completed) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.orders.review_unavailable')],
                ]);
            }

            if ($this->reviews->existsForOrder($orderId)) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.orders.review_already_exists')],
                ]);
            }

            $item = $order['items'][0] ?? null;
            if ($item === null || $item['product_id'] === null) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.orders.review_unavailable')],
                ]);
            }

            return $this->reviews->createForUserOrder([
                'order_id' => $orderId,
                'user_id' => $userId,
                'store_id' => $order['store_id'],
                'product_id' => $item['product_id'],
                'rating' => (int) $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_visible' => true,
            ]);
        });
    }

    /** @param array<string, mixed> $data */
    public function requestReturn(
        int $userId,
        int $orderId,
        array $data,
        ReturnResolutionTypeEnum $resolutionType,
    ): Model {
        $storedPaths = [];

        try {
            return $this->database->transaction(function () use ($userId, $orderId, $data, $resolutionType, &$storedPaths): Model {
                $order = $this->orders->lockUserOrderSnapshot($orderId, $userId);
                $this->assertReturnAvailable($order, $orderId, $resolutionType);

                if ($order['items'] === []) {
                    throw ValidationException::withMessages([
                        'order' => [__('messages.orders.return_unavailable')],
                    ]);
                }

                $returnRequestId = $this->returnRequests->createForUserOrder([
                    'public_id' => (string) Str::uuid(),
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'store_id' => $order['store_id'],
                    'status' => ReturnStatusEnum::PendingReview,
                    'reason' => trim($data['reason']),
                    'details' => trim($data['details']),
                    'requested_amount' => $order['total_amount'],
                    'resolution_type' => $resolutionType,
                ]);

                $mediaRows = [];
                foreach (($data['photos'] ?? []) as $index => $photo) {
                    if (! $photo instanceof UploadedFile) {
                        continue;
                    }

                    $path = $this->filesystem->disk('public')->putFile('order-returns', $photo);
                    if (! is_string($path)) {
                        throw ValidationException::withMessages([
                            'photos' => [__('messages.orders.return_photo_upload_failed')],
                        ]);
                    }

                    $storedPaths[] = $path;
                    $mediaRows[] = [
                        'return_request_id' => $returnRequestId,
                        'path' => $path,
                        'mime_type' => $photo->getMimeType(),
                        'sort_order' => $index,
                    ];
                }

                if ($mediaRows !== []) {
                    $this->returnMedia->createManyForRequest($mediaRows);
                }

                return $this->returnRequests->findForUserOrder($returnRequestId, $userId);
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                $this->filesystem->disk('public')->delete($storedPaths);
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $order */
    private function assertReturnAvailable(array $order, int $orderId, ReturnResolutionTypeEnum $resolutionType): void
    {
        $policy = $order['policy_snapshot'] ?? [];
        $enabled = $resolutionType === ReturnResolutionTypeEnum::Refund
            ? (bool) ($policy['returns_enabled'] ?? false)
            : (bool) ($policy['exchanges_enabled'] ?? false);

        if (OrderStatusEnum::from($order['status']) !== OrderStatusEnum::Completed || ! $enabled) {
            throw ValidationException::withMessages([
                'order' => [__('messages.orders.return_unavailable')],
            ]);
        }

        $completedAt = $order['completed_at'];
        $windowDays = (int) ($policy['return_window_days'] ?? 0);
        if ($completedAt === null || $windowDays < 1 || Carbon::parse($completedAt)->addDays($windowDays)->isPast()) {
            throw ValidationException::withMessages([
                'order' => [__('messages.orders.return_window_expired')],
            ]);
        }

        if ($this->returnRequests->existsForOrder($orderId)) {
            throw ValidationException::withMessages([
                'order' => [__('messages.orders.return_request_already_exists')],
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

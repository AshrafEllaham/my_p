<?php

namespace App\Services\Sai;

use App\Enums\CouponDiscountTypeEnum;
use App\Enums\CouponStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use App\Repositories\Sai\CouponRedemptionRepository;
use App\Repositories\Sai\CouponRepository;
use App\Repositories\Sai\OrderItemRepository;
use App\Repositories\Sai\OrderReceiptRepository;
use App\Repositories\Sai\OrderRepository;
use App\Repositories\Sai\OrderStatusHistoryRepository;
use App\Repositories\Sai\PaymentRepository;
use App\Repositories\Sai\ProductRepository;
use App\Repositories\Sai\StorePolicyVersionRepository;
use App\Repositories\Sai\WalletRepository;
use App\Repositories\Sai\WalletTransactionRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderCheckoutService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderItemRepository $items,
        private readonly OrderStatusHistoryRepository $history,
        private readonly OrderReceiptRepository $receipts,
        private readonly ProductRepository $products,
        private readonly StorePolicyVersionRepository $policies,
        private readonly CouponRepository $coupons,
        private readonly CouponRedemptionRepository $redemptions,
        private readonly WalletRepository $wallets,
        private readonly WalletTransactionRepository $walletTransactions,
        private readonly PaymentRepository $payments,
        private readonly DatabaseManager $database,
    ) {}

    /** @param array{product_id: int|string, quantity: int|string, coupon_code?: string|null, idempotency_key: string} $data */
    public function create(int $userId, array $data): Model
    {
        $productId = (int) $data['product_id'];
        $quantity = (int) $data['quantity'];
        $couponCode = isset($data['coupon_code']) ? mb_strtoupper(trim($data['coupon_code'])) : null;
        $idempotencyKey = "order-payment-{$userId}-{$data['idempotency_key']}";

        return $this->database->transaction(function () use ($userId, $productId, $quantity, $couponCode, $idempotencyKey): Model {
            $this->wallets->lockOwnerForUpdate($userId);

            $existingPayment = $this->payments->orderPaymentSnapshotByIdempotencyKeyForUpdate($idempotencyKey);
            if ($existingPayment !== null) {
                $metadata = $existingPayment['metadata'] ?? [];
                if (
                    $existingPayment['status'] !== PaymentStatusEnum::Paid
                    || (int) ($metadata['product_id'] ?? 0) !== $productId
                    || (int) ($metadata['quantity'] ?? 0) !== $quantity
                    || ($metadata['coupon_code'] ?? null) !== $couponCode
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => [__('messages.orders.idempotency_conflict')],
                    ]);
                }

                return $this->orders->findForUser($existingPayment['order_id'], $userId);
            }

            $product = $this->products->purchasableSnapshotForUpdate($productId, $quantity);
            if ($product === null) {
                throw ValidationException::withMessages([
                    'product_id' => [__('messages.orders.product_unavailable')],
                ]);
            }

            $policy = $this->policies->currentSnapshotForStore($product['store_id']);
            if ($policy === null) {
                throw ValidationException::withMessages([
                    'product_id' => [__('messages.orders.store_policy_unavailable')],
                ]);
            }

            $subtotalCents = $this->moneyToCents($product['price']) * $quantity;
            $coupon = $couponCode === null ? null : $this->validatedCoupon($couponCode, $product['store_id'], $userId, $subtotalCents);
            $discountCents = $coupon === null ? 0 : $this->couponDiscountCents($coupon, $subtotalCents);
            $totalCents = $subtotalCents - $discountCents;
            $wallet = $this->wallets->activeSnapshotForOwnerForUpdate($userId);

            if ($wallet === null || $wallet['currency'] !== 'EGP' || $this->moneyToCents($wallet['available_balance']) < $totalCents) {
                throw ValidationException::withMessages([
                    'wallet' => [__('messages.orders.wallet_insufficient')],
                ]);
            }

            if (! $this->products->reserveStock($productId, $quantity)) {
                throw ValidationException::withMessages([
                    'product_id' => [__('messages.orders.product_unavailable')],
                ]);
            }

            $now = now();
            $number = 'SA-'.Str::upper(Str::random(8));
            $orderPublicId = (string) Str::uuid();
            $subtotal = $this->centsToMoney($subtotalCents);
            $discount = $this->centsToMoney($discountCents);
            $total = $this->centsToMoney($totalCents);
            $balanceAfter = $this->centsToMoney($this->moneyToCents($wallet['available_balance']) - $totalCents);
            $orderId = $this->orders->createForUser([
                'public_id' => $orderPublicId,
                'number' => $number,
                'user_id' => $userId,
                'store_id' => $product['store_id'],
                'coupon_id' => $coupon['id'] ?? null,
                'store_policy_version_id' => $policy['id'],
                'status' => OrderStatusEnum::PendingStore,
                'payment_status' => PaymentStatusEnum::Paid,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'platform_fee_amount' => '0.00',
                'merchant_net_amount' => $total,
                'currency' => 'EGP',
                'policy_snapshot' => $policy,
            ]);

            $this->items->createForOrder([
                'order_id' => $orderId,
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'product_image' => $product['image'],
                'unit_price' => $product['price'],
                'quantity' => $quantity,
                'line_total' => $subtotal,
            ]);

            $walletTransactionId = null;
            if ($totalCents > 0) {
                $this->wallets->updateBalancesById($wallet['id'], $balanceAfter, $wallet['pending_balance']);
                $walletTransactionId = $this->walletTransactions->createOrderPurchase([
                    'public_id' => (string) Str::uuid(),
                    'wallet_id' => $wallet['id'],
                    'type' => WalletTransactionTypeEnum::Purchase,
                    'status' => WalletTransactionStatusEnum::Completed,
                    'amount' => $total,
                    'balance_before' => $wallet['available_balance'],
                    'balance_after' => $balanceAfter,
                    'currency' => $wallet['currency'],
                    'idempotency_key' => "{$idempotencyKey}-wallet",
                    'metadata' => ['order_number' => $number],
                    'completed_at' => $now,
                ], $orderId);
            }

            $this->payments->createOrderPayment([
                'public_id' => (string) Str::uuid(),
                'order_id' => $orderId,
                'wallet_transaction_id' => $walletTransactionId,
                'status' => PaymentStatusEnum::Paid,
                'method' => PaymentMethodEnum::Wallet,
                'amount' => $total,
                'currency' => 'EGP',
                'idempotency_key' => $idempotencyKey,
                'metadata' => [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'coupon_code' => $couponCode,
                ],
                'paid_at' => $now,
            ]);

            $this->receipts->createForOrder([
                'order_id' => $orderId,
                'receipt_number' => $number.'-R',
                'snapshot' => [
                    'order_number' => $number,
                    'store_id' => $product['store_id'],
                    'store_name' => $product['store_name'],
                    'product_name' => $product['name'],
                    'product_image' => $product['image'],
                    'quantity' => $quantity,
                    'unit_price' => $product['price'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'coupon_code' => $coupon['code'] ?? null,
                    'total_amount' => $total,
                    'currency' => 'EGP',
                    'payment_method' => PaymentMethodEnum::Wallet->value,
                    'paid_at' => $now->toIso8601String(),
                ],
                'issued_at' => $now,
            ]);

            $this->history->store([
                'order_id' => $orderId,
                'from_status' => null,
                'to_status' => OrderStatusEnum::PendingStore,
                'actor_id' => $userId,
                'note' => null,
                'metadata' => ['source' => 'user_checkout'],
            ]);

            if ($coupon !== null) {
                $this->coupons->incrementUsesCount($coupon['id']);
                $this->redemptions->store([
                    'coupon_id' => $coupon['id'],
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'discount_amount' => $discount,
                ]);
            }

            return $this->orders->findForUser($orderId, $userId);
        });
    }

    /** @return array<string, mixed> */
    private function validatedCoupon(string $code, int $storeOwnerId, int $userId, int $subtotalCents): array
    {
        $coupon = $this->coupons->checkoutSnapshotForUpdate($code, $storeOwnerId);
        $startsAt = $coupon['starts_at'] ?? null;
        $expiresAt = $coupon['expires_at'] ?? null;

        if (
            $coupon === null
            || $coupon['status'] !== CouponStatusEnum::Active->value
            || ($startsAt !== null && now()->lt(Carbon::parse($startsAt)))
            || ($expiresAt !== null && now()->gt(Carbon::parse($expiresAt)))
            || ($coupon['max_uses'] !== null && $coupon['uses_count'] >= $coupon['max_uses'])
        ) {
            throw ValidationException::withMessages([
                'coupon_code' => [__('messages.orders.coupon_unavailable')],
            ]);
        }

        if ($subtotalCents < $this->moneyToCents($coupon['minimum_order_amount'])) {
            throw ValidationException::withMessages([
                'coupon_code' => [__('messages.orders.coupon_minimum_not_met')],
            ]);
        }

        if ($this->redemptions->countForCouponUser($coupon['id'], $userId) >= $coupon['max_uses_per_user']) {
            throw ValidationException::withMessages([
                'coupon_code' => [__('messages.orders.coupon_user_limit_reached')],
            ]);
        }

        return $coupon;
    }

    /** @param array<string, mixed> $coupon */
    private function couponDiscountCents(array $coupon, int $subtotalCents): int
    {
        $discountCents = match (CouponDiscountTypeEnum::from($coupon['discount_type'])) {
            CouponDiscountTypeEnum::Percentage => intdiv(
                $subtotalCents * $this->moneyToCents($coupon['discount_value']) + 5000,
                10000,
            ),
            CouponDiscountTypeEnum::Fixed => $this->moneyToCents($coupon['discount_value']),
        };

        if ($coupon['maximum_discount_amount'] !== null) {
            $discountCents = min($discountCents, $this->moneyToCents($coupon['maximum_discount_amount']));
        }

        return min($discountCents, $subtotalCents);
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

<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Repositories\Sai\OrderRepository;
use App\Repositories\Sai\OrderStatusHistoryRepository;
use App\Repositories\Sai\UserRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreOrderLifecycleService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderStatusHistoryRepository $history,
        private readonly UserRepository $users,
        private readonly DatabaseManager $database,
    ) {}

    /** @param array<string, mixed> $filters */
    public function list(int $storeOwnerId, array $filters): Builder
    {
        $this->assertStoreOwner($storeOwnerId);

        return $this->orders->listForStore($storeOwnerId, $filters);
    }

    public function find(int $storeOwnerId, int $orderId): Model
    {
        $this->assertStoreOwner($storeOwnerId);

        return $this->orders->findForStore($orderId, $storeOwnerId);
    }

    public function transition(int $storeOwnerId, int $orderId, OrderStatusEnum $nextStatus): Model
    {
        $this->assertStoreOwner($storeOwnerId);

        return $this->database->transaction(function () use ($storeOwnerId, $orderId, $nextStatus): Model {
            $order = $this->orders->lockStoreOrderSnapshot($orderId, $storeOwnerId);
            $currentStatus = OrderStatusEnum::from($order['status']);

            if ($currentStatus === $nextStatus) {
                return $this->orders->findForStore($orderId, $storeOwnerId);
            }

            $allowedNextStatus = match ($currentStatus) {
                OrderStatusEnum::PendingStore => OrderStatusEnum::Confirmed,
                OrderStatusEnum::Confirmed => OrderStatusEnum::Preparing,
                OrderStatusEnum::Preparing => OrderStatusEnum::ReadyForCompletion,
                default => null,
            };

            if ($allowedNextStatus !== $nextStatus) {
                throw ValidationException::withMessages([
                    'status' => [__('messages.orders.store_status_transition_unavailable')],
                ]);
            }

            $now = now();
            $updates = ['status' => $nextStatus->value];
            if ($nextStatus === OrderStatusEnum::Confirmed) {
                $updates['confirmed_at'] = $now;
            } elseif ($nextStatus === OrderStatusEnum::Preparing) {
                $updates['preparing_at'] = $now;
            } else {
                $holdHours = max(1, (int) ($order['policy_snapshot']['pickup_hold_hours'] ?? 48));
                $updates['ready_at'] = $now;
                $updates['customer_confirmation_requested_at'] = $now;
                $updates['pickup_expires_at'] = $now->copy()->addHours($holdHours);
                $updates['pickup_qr_token'] = (string) Str::uuid();
            }

            $this->orders->updateOrder($orderId, $updates);
            $this->history->createRecord([
                'order_id' => $orderId,
                'from_status' => $currentStatus,
                'to_status' => $nextStatus,
                'actor_id' => $storeOwnerId,
                'note' => null,
                'metadata' => null,
            ]);

            return $this->orders->findForStore($orderId, $storeOwnerId);
        });
    }

    public function confirmPickup(int $storeOwnerId, int $orderId, string $pickupQrToken): Model
    {
        $this->assertStoreOwner($storeOwnerId);

        return $this->database->transaction(function () use ($storeOwnerId, $orderId, $pickupQrToken): Model {
            $order = $this->orders->lockStoreOrderSnapshot($orderId, $storeOwnerId);
            $currentStatus = OrderStatusEnum::from($order['status']);
            if ($currentStatus === OrderStatusEnum::Completed) {
                return $this->orders->findForStore($orderId, $storeOwnerId);
            }

            $storedToken = (string) ($order['pickup_qr_token'] ?? '');
            if (! in_array($currentStatus, [OrderStatusEnum::ReadyForCompletion, OrderStatusEnum::AwaitingCustomerConfirmation], true)
                || $storedToken === ''
                || ! hash_equals($storedToken, $pickupQrToken)) {
                throw ValidationException::withMessages([
                    'pickup_qr_token' => [__('messages.orders.pickup_code_invalid')],
                ]);
            }

            $this->orders->updateOrder($orderId, [
                'status' => OrderStatusEnum::Completed->value,
                'completed_at' => now(),
                'pickup_qr_token' => null,
            ]);
            $this->history->createRecord([
                'order_id' => $orderId,
                'from_status' => $currentStatus,
                'to_status' => OrderStatusEnum::Completed,
                'actor_id' => $storeOwnerId,
                'note' => __('messages.orders.pickup_confirmed'),
                'metadata' => null,
            ]);

            return $this->orders->findForStore($orderId, $storeOwnerId);
        });
    }

    private function assertStoreOwner(int $storeOwnerId): void
    {
        $owner = $this->users->findOrFail($storeOwnerId);
        if ($owner->account_type !== AccountTypeEnum::Store) {
            throw ValidationException::withMessages([
                'account_type' => [__('messages.profile.store_type_required')],
            ]);
        }
    }
}

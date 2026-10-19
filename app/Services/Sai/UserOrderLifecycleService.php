<?php

namespace App\Services\Sai;

use App\Enums\DisputeStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Sai\OrderDispute;
use App\Models\Sai\User;
use App\Repositories\Sai\OrderDisputeMessageRepository;
use App\Repositories\Sai\OrderDisputeRepository;
use App\Repositories\Sai\OrderRepository;
use App\Repositories\Sai\OrderStatusHistoryRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserOrderLifecycleService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderDisputeRepository $disputes,
        private readonly OrderDisputeMessageRepository $messages,
        private readonly OrderStatusHistoryRepository $history,
        private readonly DatabaseManager $database,
    ) {}

    public function confirmCompletion(int $userId, int $orderId): Model
    {
        return $this->database->transaction(function () use ($userId, $orderId): Model {
            $order = $this->orders->lockUserOrderSnapshot($orderId, $userId);
            $status = OrderStatusEnum::from($order['status']);

            if ($status === OrderStatusEnum::Completed) {
                return $this->orders->findForUser($orderId, $userId);
            }

            if (! in_array($status, [OrderStatusEnum::ReadyForCompletion, OrderStatusEnum::AwaitingCustomerConfirmation], true)) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.orders.completion_confirmation_unavailable')],
                ]);
            }

            $now = now();
            $this->orders->updateOrder($orderId, [
                'status' => OrderStatusEnum::Completed->value,
                'completed_at' => $now,
                'pickup_qr_token' => null,
            ]);
            $this->history->createRecord([
                'order_id' => $orderId,
                'from_status' => $status,
                'to_status' => OrderStatusEnum::Completed,
                'actor_id' => $userId,
                'note' => __('messages.orders.customer_completion_confirmed'),
                'metadata' => null,
            ]);

            return $this->orders->findForUser($orderId, $userId);
        });
    }

    /** @param array{reason: string, details: string} $data */
    public function openDispute(int $userId, int $orderId, array $data): OrderDispute
    {
        return $this->database->transaction(function () use ($userId, $orderId, $data): OrderDispute {
            $order = $this->orders->lockUserOrderSnapshot($orderId, $userId);
            if ($this->disputes->existsForOrder($orderId)) {
                return $this->disputes->findForUserOrder($orderId, $userId);
            }

            $status = OrderStatusEnum::from($order['status']);
            if (! in_array($status, [OrderStatusEnum::ReadyForCompletion, OrderStatusEnum::AwaitingCustomerConfirmation], true)) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.orders.dispute_unavailable')],
                ]);
            }

            $dispute = $this->disputes->createRecord([
                'public_id' => (string) Str::uuid(),
                'order_id' => $orderId,
                'opened_by' => $userId,
                'store_id' => $order['store_id'],
                'status' => DisputeStatusEnum::Open,
                'reason' => $data['reason'],
                'details' => $data['details'],
            ]);
            $this->orders->updateOrder($orderId, ['status' => OrderStatusEnum::Disputed->value]);
            $this->history->createRecord([
                'order_id' => $orderId,
                'from_status' => $status,
                'to_status' => OrderStatusEnum::Disputed,
                'actor_id' => $userId,
                'note' => __('messages.orders.dispute_opened_history'),
                'metadata' => null,
            ]);

            return $this->disputes->findForUserOrder($orderId, $userId);
        });
    }

    public function dispute(int $userId, int $orderId): OrderDispute
    {
        return $this->disputes->findForUserOrder($orderId, $userId);
    }

    /** @param array{body: string} $data */
    public function replyToDispute(int $userId, int $orderId, array $data): OrderDispute
    {
        return $this->database->transaction(function () use ($userId, $orderId, $data): OrderDispute {
            $dispute = $this->disputes->findForUserOrder($orderId, $userId);
            if ($dispute->status !== DisputeStatusEnum::InformationRequested) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.orders.dispute_reply_unavailable')],
                ]);
            }

            $this->messages->createRecord([
                'order_dispute_id' => $dispute->getKey(),
                'sender_type' => User::class,
                'sender_id' => $userId,
                'body' => $data['body'],
            ]);
            $this->disputes->updateRecord($dispute->getKey(), ['status' => DisputeStatusEnum::UnderReview]);

            return $this->disputes->findForUserOrder($orderId, $userId);
        });
    }
}

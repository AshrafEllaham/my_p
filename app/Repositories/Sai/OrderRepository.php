<?php

namespace App\Repositories\Sai;

use App\Models\Sai\Order;
use App\Models\Sai\OrderItem;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends MainRepository
{
    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    /** @param array<string, mixed> $filters */
    public function listForUser(int $userId, array $filters = []): Builder
    {
        return $this->query()
            ->select([
                'id', 'public_id', 'number', 'user_id', 'store_id', 'status', 'payment_status',
                'subtotal', 'discount_amount', 'total_amount', 'currency', 'policy_snapshot', 'pickup_expires_at',
                'confirmed_at', 'preparing_at', 'ready_at', 'customer_confirmation_requested_at',
                'created_at', 'completed_at', 'cancelled_at', 'cancellation_reason',
            ])
            ->where('user_id', $userId)
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->with([
                'store:id,name',
                'items:id,order_id,product_id,product_name,product_image,unit_price,quantity,line_total',
                'returnRequest:id,order_id,public_id,status,reason,details,requested_amount,approved_amount,resolution_type,merchant_note,created_at',
                'review:id,order_id,rating,comment',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function findForUser(int|string $id, int $userId): Model
    {
        return $this->query()
            ->select([
                'id', 'public_id', 'number', 'user_id', 'store_id', 'coupon_id', 'store_policy_version_id',
                'status', 'payment_status', 'subtotal', 'discount_amount', 'total_amount',
                'platform_fee_amount', 'merchant_net_amount', 'currency', 'policy_snapshot',
                'pickup_expires_at', 'confirmed_at', 'preparing_at', 'ready_at',
                'customer_confirmation_requested_at', 'completed_at', 'cancelled_at',
                'cancellation_reason', 'created_at', 'updated_at',
            ])
            ->where('user_id', $userId)
            ->with([
                'store:id,name',
                'items:id,order_id,product_id,product_name,product_image,unit_price,quantity,line_total',
                'receipt:id,order_id,receipt_number,snapshot,pdf_path,issued_at',
                'review:id,order_id,rating,comment',
                'returnRequest:id,order_id,public_id,status,reason,details,requested_amount,approved_amount,resolution_type,merchant_note,created_at',
                'returnRequest.media:id,return_request_id,path,mime_type,sort_order',
                'statusHistory:id,order_id,from_status,to_status,actor_id,note,created_at',
            ])
            ->findOrFail($id);
    }

    /** @return array<string, mixed> */
    public function lockUserOrderSnapshot(int|string $id, int $userId): array
    {
        $order = $this->query()
            ->select([
                'id', 'user_id', 'store_id', 'status', 'payment_status', 'total_amount',
                'currency', 'policy_snapshot', 'completed_at',
            ])
            ->where('user_id', $userId)
            ->with(['items:id,order_id,product_id,product_name,product_image,unit_price,quantity,line_total'])
            ->lockForUpdate()
            ->findOrFail($id);

        return [
            'id' => (int) $order->getKey(),
            'user_id' => (int) $order->user_id,
            'store_id' => (int) $order->store_id,
            'status' => $order->status->value,
            'payment_status' => $order->payment_status->value,
            'total_amount' => (string) $order->total_amount,
            'currency' => (string) $order->currency,
            'policy_snapshot' => $order->policy_snapshot,
            'completed_at' => $order->completed_at?->toIso8601String(),
            'items' => $order->items->map(static fn (OrderItem $item): array => [
                'id' => (int) $item->getKey(),
                'product_id' => $item->product_id === null ? null : (int) $item->product_id,
                'product_name' => (string) $item->product_name,
                'line_total' => (string) $item->line_total,
                'quantity' => (int) $item->quantity,
            ])->all(),
        ];
    }

    public function createForUser(array $data): int
    {
        return (int) $this->store($data)->getKey();
    }

    public function updateForUserOrder(int $orderId, array $data): void
    {
        $this->query()->whereKey($orderId)->update($data);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
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
}

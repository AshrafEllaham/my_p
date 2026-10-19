<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'number' => $this->number,
            'status' => $this->status?->value,
            'payment_status' => $this->payment_status?->value,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'created_at' => $this->created_at?->toIso8601String(),
            'pickup_expires_at' => $this->pickup_expires_at?->toIso8601String(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'preparing_at' => $this->preparing_at?->toIso8601String(),
            'ready_at' => $this->ready_at?->toIso8601String(),
            'customer_confirmation_requested_at' => $this->customer_confirmation_requested_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'policy' => [
                'pickup_hold_hours' => (int) ($this->policy_snapshot['pickup_hold_hours'] ?? 0),
                'pickup_instructions' => $this->policy_snapshot['pickup_instructions'] ?? null,
                'cancellation_enabled' => (bool) ($this->policy_snapshot['cancellation_enabled'] ?? false),
                'returns_enabled' => (bool) ($this->policy_snapshot['returns_enabled'] ?? false),
                'exchanges_enabled' => (bool) ($this->policy_snapshot['exchanges_enabled'] ?? false),
                'return_window_days' => (int) ($this->policy_snapshot['return_window_days'] ?? 0),
            ],
            'store' => $this->whenLoaded('store', fn () => [
                'id' => $this->store?->id,
                'name' => $this->store?->name,
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'receipt' => OrderReceiptResource::make($this->whenLoaded('receipt')),
            'review' => $this->whenLoaded('review', fn () => $this->review === null ? null : [
                'rating' => $this->review->rating,
                'comment' => $this->review->comment,
            ]),
            'return_request' => ReturnRequestResource::make($this->whenLoaded('returnRequest')),
            'status_history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistory')),
        ];
    }
}

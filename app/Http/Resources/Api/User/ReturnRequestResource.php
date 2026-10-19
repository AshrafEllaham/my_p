<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReturnRequest */
class ReturnRequestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'order_id' => $this->order_id,
            'status' => $this->status?->value,
            'reason' => $this->reason,
            'details' => $this->details,
            'requested_amount' => $this->requested_amount,
            'approved_amount' => $this->approved_amount,
            'resolution_type' => $this->resolution_type?->value,
            'merchant_note' => $this->merchant_note,
            'created_at' => $this->created_at?->toIso8601String(),
            'media' => ReturnRequestMediaResource::collection($this->whenLoaded('media')),
        ];
    }
}

<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\OrderDispute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderDispute */
class OrderDisputeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'status' => $this->status?->value,
            'reason' => $this->reason,
            'details' => $this->details,
            'resolution_note' => $this->resolution_note,
            'created_at' => $this->created_at?->toIso8601String(),
            'messages' => OrderDisputeMessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}

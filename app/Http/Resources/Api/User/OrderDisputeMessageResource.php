<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\OrderDisputeMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderDisputeMessage */
class OrderDisputeMessageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'sender_type' => $this->sender_type,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

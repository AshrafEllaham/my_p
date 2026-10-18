<?php

namespace App\Http\Resources\Api\Chat;

use App\Models\Sai\ConversationSafetyAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ConversationSafetyAction */
class ChatSafetyActionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->conversation?->public_id,
            'action' => $this->action?->value,
            'reason' => $this->reason,
            'details' => $this->details,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

<?php

namespace App\Http\Resources\Api\Chat;

use App\Models\Sai\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Message */
class ChatMessageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'body' => $this->body,
            'is_mine' => (int) $this->sender_id === (int) $request->user('api')?->getAuthIdentifier(),
            'sender' => $this->whenLoaded('sender', fn () => ChatParticipantResource::make($this->sender)),
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

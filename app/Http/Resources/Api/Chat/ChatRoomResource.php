<?php

namespace App\Http\Resources\Api\Chat;

use App\Models\Sai\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Conversation */
class ChatRoomResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'user' => $this->whenLoaded('user', fn () => ChatParticipantResource::make($this->user)),
            'store' => $this->whenLoaded('store', fn () => ChatParticipantResource::make($this->store)),
            'last_message' => $this->whenLoaded('lastMessage', fn () => ChatMessageResource::make($this->lastMessage)),
            'last_message_at' => $this->last_message_at?->toISOString(),
            'unread_messages_count' => $this->when(isset($this->unread_messages_count), (int) $this->unread_messages_count),
            'is_blocked_by_me' => $this->when(isset($this->is_blocked_by_me), (bool) $this->is_blocked_by_me),
            'is_blocked_by_other' => $this->when(isset($this->is_blocked_by_other), (bool) $this->is_blocked_by_other),
        ];
    }
}

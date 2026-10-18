<?php

namespace App\Events\Sai\Chat;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class ChatMessageSent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    public function __construct(
        public readonly string $roomId,
        public readonly int $messageId,
        public readonly int $userId,
        public readonly int $storeId,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.inboxes.'.$this->userId),
            new PrivateChannel('chat.inboxes.'.$this->storeId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message.sent';
    }

    /** @return array{room_id: string, message_id: int} */
    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'message_id' => $this->messageId,
        ];
    }
}

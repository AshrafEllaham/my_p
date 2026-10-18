<?php

namespace App\Repositories\Sai;

use App\Enums\MessageTypeEnum;
use App\Models\Sai\Message;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MessageRepository extends MainRepository
{
    public function __construct(Message $model)
    {
        $this->model = $model;
    }

    public function createForConversation(int $conversationId, int $senderId, string $body): Model
    {
        return $this->getModel()->newQuery()->create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'type' => MessageTypeEnum::Text,
            'body' => $body,
        ])->load('sender:id,name,avatar');
    }

    /** @param array<string, mixed> $filters */
    public function listForConversation(int $conversationId, array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return $this->getModel()->newQuery()
            ->select(['id', 'conversation_id', 'sender_id', 'type', 'body', 'read_at', 'created_at'])
            ->where('conversation_id', $conversationId)
            ->when($search !== '', fn (Builder $query): Builder => $query->where('body', 'like', '%'.$search.'%'))
            ->with('sender:id,name,avatar')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function markIncomingAsRead(int $conversationId, int $actorId, \DateTimeInterface $at): int
    {
        return $this->getModel()->newQuery()
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $actorId)
            ->whereNull('read_at')
            ->update(['read_at' => $at]);
    }
}

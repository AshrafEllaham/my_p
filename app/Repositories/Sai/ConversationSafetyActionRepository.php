<?php

namespace App\Repositories\Sai;

use App\Enums\ConversationSafetyActionTypeEnum;
use App\Models\Sai\ConversationSafetyAction;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Model;

class ConversationSafetyActionRepository extends MainRepository
{
    public function __construct(ConversationSafetyAction $model)
    {
        $this->model = $model;
    }

    public function recordReport(
        int $conversationId,
        int $actorId,
        int $targetId,
        string $reason,
        ?string $details,
    ): Model {
        return $this->getModel()->newQuery()->updateOrCreate(
            [
                'conversation_id' => $conversationId,
                'actor_id' => $actorId,
                'action' => ConversationSafetyActionTypeEnum::Report->value,
            ],
            [
                'target_id' => $targetId,
                'reason' => $reason,
                'details' => $details,
            ],
        )->load('conversation:id,public_id');
    }

    public function recordBlock(int $conversationId, int $actorId, int $targetId): Model
    {
        return $this->getModel()->newQuery()->firstOrCreate(
            [
                'conversation_id' => $conversationId,
                'actor_id' => $actorId,
                'action' => ConversationSafetyActionTypeEnum::Block->value,
            ],
            ['target_id' => $targetId],
        )->load('conversation:id,public_id');
    }

    public function isConversationBlocked(int $conversationId): bool
    {
        return $this->getModel()->newQuery()
            ->where('conversation_id', $conversationId)
            ->where('action', ConversationSafetyActionTypeEnum::Block->value)
            ->exists();
    }
}

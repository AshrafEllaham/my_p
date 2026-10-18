<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Enums\ConversationSafetyActionTypeEnum;
use App\Models\Sai\Conversation;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ConversationRepository extends MainRepository
{
    public function __construct(Conversation $model)
    {
        $this->model = $model;
    }

    public function createOrFindForPair(int $userId, int $storeId, string $publicId): Model
    {
        return $this->getModel()->newQuery()->firstOrCreate(
            ['user_id' => $userId, 'store_id' => $storeId],
            ['public_id' => $publicId],
        );
    }

    public function findForParticipant(string $publicId, int $actorId, AccountTypeEnum $actorType): Model
    {
        return $this->participantQuery($publicId, $actorId, $actorType)
            ->with($this->participantRelations())
            ->firstOrFail();
    }

    public function lockForParticipant(string $publicId, int $actorId, AccountTypeEnum $actorType): Model
    {
        return $this->participantQuery($publicId, $actorId, $actorType)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /** @param array<string, mixed> $filters */
    public function listForParticipant(
        int $actorId,
        AccountTypeEnum $actorType,
        array $filters,
    ): Builder {
        $query = $this->getModel()->newQuery()
            ->select(['id', 'public_id', 'user_id', 'store_id', 'last_message_at', 'created_at', 'updated_at'])
            ->with($this->participantRelations())
            ->withCount(['messages as unread_messages_count' => fn (Builder $messages): Builder => $messages
                ->whereNull('read_at')
                ->where('sender_id', '!=', $actorId)])
            ->withExists([
                'safetyActions as is_blocked_by_me' => fn (Builder $actions): Builder => $actions
                    ->where('action', ConversationSafetyActionTypeEnum::Block->value)
                    ->where('actor_id', $actorId),
                'safetyActions as is_blocked_by_other' => fn (Builder $actions): Builder => $actions
                    ->where('action', ConversationSafetyActionTypeEnum::Block->value)
                    ->where('actor_id', '!=', $actorId),
            ]);

        if ($actorType === AccountTypeEnum::User) {
            $query->where('user_id', $actorId);
            $peerRelation = 'store';
        } else {
            $query->where('store_id', $actorId);
            $peerRelation = 'user';
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->whereHas($peerRelation, fn (Builder $peer): Builder => $peer
                ->where('name', 'like', '%'.$search.'%'));
        }

        return $query
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');
    }

    public function touchForMessage(Model $conversation, \DateTimeInterface $at): void
    {
        $conversation->forceFill([
            'last_message_at' => $at,
            'updated_at' => $at,
        ])->save();
    }

    public function markParticipantRead(Model $conversation, AccountTypeEnum $actorType, \DateTimeInterface $at): void
    {
        $column = $actorType === AccountTypeEnum::User ? 'user_read_at' : 'store_read_at';
        $conversation->forceFill([$column => $at])->save();
    }

    private function participantQuery(string $publicId, int $actorId, AccountTypeEnum $actorType): Builder
    {
        $query = $this->getModel()->newQuery()->where('public_id', $publicId);

        return $actorType === AccountTypeEnum::User
            ? $query->where('user_id', $actorId)
            : $query->where('store_id', $actorId);
    }

    /** @return array<string, mixed> */
    private function participantRelations(): array
    {
        return [
            'user:id,name,avatar,account_type',
            'store:id,name,avatar,account_type',
            'lastMessage' => fn ($query) => $query->select([
                'messages.id',
                'messages.conversation_id',
                'messages.sender_id',
                'messages.type',
                'messages.body',
                'messages.created_at',
                'messages.read_at',
            ]),
            'lastMessage.sender:id,name,avatar',
        ];
    }
}

<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Repositories\Sai\ConversationSafetyActionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConversationSafetyActionService
{
    public function __construct(
        private readonly ConversationService $conversations,
        private readonly ConversationSafetyActionRepository $actions,
    ) {}

    public function report(
        int $actorId,
        ?AccountTypeEnum $actorType,
        string $roomId,
        string $reason,
        ?string $details,
    ): Model {
        return DB::transaction(function () use ($actorId, $actorType, $roomId, $reason, $details): Model {
            $room = $this->conversations->lockRoomForParticipant($roomId, $actorId, $actorType);

            return $this->actions->recordReport(
                (int) $room->id,
                $actorId,
                $this->targetId($room, $actorId),
                $reason,
                $details,
            );
        });
    }

    public function block(int $actorId, ?AccountTypeEnum $actorType, string $roomId): Model
    {
        return DB::transaction(function () use ($actorId, $actorType, $roomId): Model {
            $room = $this->conversations->lockRoomForParticipant($roomId, $actorId, $actorType);

            return $this->actions->recordBlock(
                (int) $room->id,
                $actorId,
                $this->targetId($room, $actorId),
            );
        });
    }

    private function targetId(Model $room, int $actorId): int
    {
        return (int) ((int) $room->user_id === $actorId ? $room->store_id : $room->user_id);
    }
}

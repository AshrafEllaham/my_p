<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Repositories\Sai\ConversationRepository;
use App\Repositories\Sai\StoreRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConversationService
{
    public function __construct(
        private readonly ConversationRepository $conversations,
        private readonly StoreRepository $stores,
    ) {}

    public function createRoom(int $actorId, ?AccountTypeEnum $actorType, int $storeId): Model
    {
        if ($actorType !== AccountTypeEnum::User) {
            throw new AuthorizationException(__('messages.chat.user_only'));
        }

        $storeOwnerId = $this->stores->ensureActiveOwnerForChat($storeId);
        $publicId = (string) Str::uuid();

        return DB::transaction(function () use ($actorId, $storeOwnerId, $publicId): Model {
            $conversation = $this->conversations->createOrFindForPair($actorId, $storeOwnerId, $publicId);

            return $this->conversations->findForParticipant(
                (string) $conversation->public_id,
                $actorId,
                AccountTypeEnum::User,
            );
        });
    }

    /** @param array<string, mixed> $filters */
    public function listRooms(int $actorId, ?AccountTypeEnum $actorType, array $filters): Builder
    {
        if (! in_array($actorType, [AccountTypeEnum::User, AccountTypeEnum::Store], true)) {
            throw new AuthorizationException(__('messages.chat.account_type_required'));
        }

        return $this->conversations->listForParticipant($actorId, $actorType, $filters);
    }

    public function lockRoomForParticipant(
        string $publicId,
        int $actorId,
        ?AccountTypeEnum $actorType,
    ): Model {
        if (! in_array($actorType, [AccountTypeEnum::User, AccountTypeEnum::Store], true)) {
            throw new AuthorizationException(__('messages.chat.account_type_required'));
        }

        return $this->conversations->lockForParticipant($publicId, $actorId, $actorType);
    }
}

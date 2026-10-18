<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Events\Sai\Chat\ChatMessageSent;
use App\Repositories\Sai\ConversationRepository;
use App\Repositories\Sai\ConversationSafetyActionRepository;
use App\Repositories\Sai\MessageRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function __construct(
        private readonly ConversationService $conversations,
        private readonly ConversationRepository $conversationRepository,
        private readonly MessageRepository $messages,
        private readonly ConversationSafetyActionRepository $safetyActions,
        private readonly Dispatcher $events,
    ) {}

    public function send(
        int $actorId,
        ?AccountTypeEnum $actorType,
        string $roomId,
        string $body,
    ): Model {
        /** @var array{message: Model, room_id: string, user_id: int, store_id: int} $sent */
        $sent = DB::transaction(function () use ($actorId, $actorType, $roomId, $body): array {
            $room = $this->conversations->lockRoomForParticipant($roomId, $actorId, $actorType);

            if ($this->safetyActions->isConversationBlocked((int) $room->id)) {
                throw new AuthorizationException(__('messages.chat.room_blocked'));
            }

            $message = $this->messages->createForConversation(
                (int) $room->id,
                $actorId,
                trim($body),
            );
            $this->conversationRepository->touchForMessage($room, now());

            return [
                'message' => $message,
                'room_id' => (string) $room->public_id,
                'user_id' => (int) $room->user_id,
                'store_id' => (int) $room->store_id,
            ];
        });

        $this->events->dispatch(new ChatMessageSent(
            $sent['room_id'],
            (int) $sent['message']->id,
            $sent['user_id'],
            $sent['store_id'],
        ));

        return $sent['message'];
    }

    /** @param array<string, mixed> $filters */
    public function list(
        int $actorId,
        ?AccountTypeEnum $actorType,
        string $roomId,
        array $filters,
    ): Builder {
        $conversationId = DB::transaction(function () use ($actorId, $actorType, $roomId): int {
            $room = $this->conversations->lockRoomForParticipant($roomId, $actorId, $actorType);
            $readAt = now();

            $this->messages->markIncomingAsRead((int) $room->id, $actorId, $readAt);
            $readerType = (int) $room->user_id === $actorId
                ? AccountTypeEnum::User
                : AccountTypeEnum::Store;
            $this->conversationRepository->markParticipantRead($room, $readerType, $readAt);

            return (int) $room->id;
        });

        return $this->messages->listForConversation($conversationId, $filters);
    }
}

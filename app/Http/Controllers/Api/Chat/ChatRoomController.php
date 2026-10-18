<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\BlockChatRoomRequest;
use App\Http\Requests\Api\Chat\CreateChatRoomRequest;
use App\Http\Requests\Api\Chat\ListChatRoomsRequest;
use App\Http\Requests\Api\Chat\ReportChatRoomRequest;
use App\Http\Resources\Api\Chat\ChatRoomResource;
use App\Http\Resources\Api\Chat\ChatSafetyActionResource;
use App\Models\Sai\User;
use App\Services\Sai\ConversationSafetyActionService;
use App\Services\Sai\ConversationService;
use Illuminate\Http\JsonResponse;

class ChatRoomController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversations,
        private readonly ConversationSafetyActionService $safetyActions,
    ) {}

    public function index(ListChatRoomsRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $rooms = $this->conversations->listRooms(
            (int) $user->id,
            $user->account_type,
            $request->validated(),
        );

        return generalReturn($request, $rooms, ChatRoomResource::class, __('messages.chat.rooms_listed'));
    }

    public function store(CreateChatRoomRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $room = $this->conversations->createRoom(
            (int) $user->id,
            $user->account_type,
            (int) $request->validated('store_id'),
        );

        return jsonSuccess(ChatRoomResource::make($room), __('messages.chat.room_created'));
    }

    public function report(ReportChatRoomRequest $request, string $room): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();
        $data = $request->validated();
        $action = $this->safetyActions->report(
            (int) $user->id,
            $user->account_type,
            $data['room'],
            $data['reason'],
            $data['details'] ?? null,
        );

        return jsonSuccess(ChatSafetyActionResource::make($action), __('messages.chat.room_reported'));
    }

    public function block(BlockChatRoomRequest $request, string $room): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();
        $action = $this->safetyActions->block(
            (int) $user->id,
            $user->account_type,
            $request->validated('room'),
        );

        return jsonSuccess(ChatSafetyActionResource::make($action), __('messages.chat.room_blocked_success'));
    }
}

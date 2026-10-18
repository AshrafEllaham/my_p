<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\ListChatMessagesRequest;
use App\Http\Requests\Api\Chat\SendChatMessageRequest;
use App\Http\Resources\Api\Chat\ChatMessageResource;
use App\Models\Sai\User;
use App\Services\Sai\MessageService;
use Illuminate\Http\JsonResponse;

class ChatMessageController extends Controller
{
    public function __construct(private readonly MessageService $messages) {}

    public function index(ListChatMessagesRequest $request, string $room): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();
        $messages = $this->messages->list(
            (int) $user->id,
            $user->account_type,
            $request->validated('room'),
            $request->validated(),
        );

        return generalReturn($request, $messages, ChatMessageResource::class, __('messages.chat.messages_listed'));
    }

    public function store(SendChatMessageRequest $request, string $room): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();
        $data = $request->validated();
        $message = $this->messages->send(
            (int) $user->id,
            $user->account_type,
            $data['room'],
            $data['body'],
        );

        return jsonSuccess(ChatMessageResource::make($message), __('messages.chat.message_sent'));
    }
}

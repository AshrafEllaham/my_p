<?php

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\ApiRequest;

abstract class ChatRoomActionRequest extends ApiRequest
{
    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return [...parent::validationData(), 'room' => $this->route('room')];
    }

    /** @return array<string, array<int, mixed>> */
    protected function roomRules(): array
    {
        return ['room' => ['required', 'string', 'uuid']];
    }

    /** @return array<string, string> */
    protected function roomAttributes(): array
    {
        return ['room' => __('messages.validation.chat.room.label')];
    }

    /** @return array<string, string> */
    protected function roomMessages(): array
    {
        return [
            'room.required' => __('messages.validation.chat.room.required'),
            'room.string' => __('messages.validation.chat.room.string'),
            'room.uuid' => __('messages.validation.chat.room.uuid'),
        ];
    }
}

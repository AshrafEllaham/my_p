<?php

namespace App\Http\Requests\Api\Chat;

class ReportChatRoomRequest extends ChatRoomActionRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->roomRules() + [
            'reason' => ['required', 'string', 'max:120'],
            'details' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return $this->roomAttributes() + [
            'reason' => __('messages.validation.chat.reason.label'),
            'details' => __('messages.validation.chat.details.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->roomMessages() + [
            'reason.required' => __('messages.validation.chat.reason.required'),
            'reason.string' => __('messages.validation.chat.reason.string'),
            'reason.max' => __('messages.validation.chat.reason.max'),
            'details.string' => __('messages.validation.chat.details.string'),
            'details.max' => __('messages.validation.chat.details.max'),
        ];
    }
}

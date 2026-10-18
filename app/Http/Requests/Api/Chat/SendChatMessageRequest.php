<?php

namespace App\Http\Requests\Api\Chat;

class SendChatMessageRequest extends ChatRoomActionRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (is_string($this->input('body'))) {
            $this->merge(['body' => trim($this->input('body'))]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->roomRules() + [
            'body' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return $this->roomAttributes() + [
            'body' => __('messages.validation.chat.body.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->roomMessages() + [
            'body.required' => __('messages.validation.chat.body.required'),
            'body.string' => __('messages.validation.chat.body.string'),
            'body.min' => __('messages.validation.chat.body.min'),
            'body.max' => __('messages.validation.chat.body.max'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Chat;

class BlockChatRoomRequest extends ChatRoomActionRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->roomRules();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return $this->roomAttributes();
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->roomMessages();
    }
}

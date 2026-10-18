<?php

namespace App\Http\Requests\Api\Chat;

use App\Enums\AccountTypeEnum;
use App\Enums\UserStatusEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class CreateChatRoomRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'store_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('users', 'id')
                    ->where('account_type', AccountTypeEnum::Store->value)
                    ->where('status', UserStatusEnum::Active->value)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['store_id' => __('messages.validation.chat.store_id.label')];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'store_id.required' => __('messages.validation.chat.store_id.required'),
            'store_id.integer' => __('messages.validation.chat.store_id.integer'),
            'store_id.min' => __('messages.validation.chat.store_id.min'),
            'store_id.exists' => __('messages.validation.chat.store_id.exists'),
        ];
    }
}

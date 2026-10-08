<?php

namespace App\Http\Requests\Api;

use App\Enums\AccountTypeEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListBannersRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::enum(AccountTypeEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'type.string' => __('messages.validation.banner_type.string'),
            'type.enum' => __('messages.validation.banner_type.enum'),
        ];
    }
}

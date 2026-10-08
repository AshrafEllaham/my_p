<?php

namespace App\Http\Requests\Api;

use App\Enums\AccountTypeEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListFaqsRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::enum(AccountTypeEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'type' => __('messages.validation.faq_type.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'type.string' => __('messages.validation.faq_type.string'),
            'type.enum' => __('messages.validation.faq_type.enum'),
        ];
    }
}

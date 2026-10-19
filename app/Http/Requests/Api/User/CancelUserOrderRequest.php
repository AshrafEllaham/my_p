<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class CancelUserOrderRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['reason' => ['nullable', 'string', 'max:500']];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['reason' => __('messages.validation.user_orders.cancel_reason.label')];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reason.string' => __('messages.validation.user_orders.cancel_reason.string'),
            'reason.max' => __('messages.validation.user_orders.cancel_reason.max'),
        ];
    }
}

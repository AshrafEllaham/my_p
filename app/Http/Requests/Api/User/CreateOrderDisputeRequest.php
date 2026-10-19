<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class CreateOrderDisputeRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'reason' => __('messages.validation.order_dispute.reason.label'),
            'details' => __('messages.validation.order_dispute.details.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reason.required' => __('messages.validation.order_dispute.reason.required'),
            'reason.string' => __('messages.validation.order_dispute.reason.string'),
            'reason.max' => __('messages.validation.order_dispute.reason.max'),
            'details.required' => __('messages.validation.order_dispute.details.required'),
            'details.string' => __('messages.validation.order_dispute.details.string'),
            'details.min' => __('messages.validation.order_dispute.details.min'),
            'details.max' => __('messages.validation.order_dispute.details.max'),
        ];
    }
}

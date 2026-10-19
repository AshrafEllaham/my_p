<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class ReplyOrderDisputeRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:10', 'max:5000']];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['body' => __('messages.validation.order_dispute.reply.label')];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'body.required' => __('messages.validation.order_dispute.reply.required'),
            'body.string' => __('messages.validation.order_dispute.reply.string'),
            'body.min' => __('messages.validation.order_dispute.reply.min'),
            'body.max' => __('messages.validation.order_dispute.reply.max'),
        ];
    }
}

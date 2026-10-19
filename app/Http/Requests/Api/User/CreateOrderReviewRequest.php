<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class CreateOrderReviewRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'rating' => __('messages.validation.user_orders.rating.label'),
            'comment' => __('messages.validation.user_orders.review_comment.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'rating.required' => __('messages.validation.user_orders.rating.required'),
            'rating.integer' => __('messages.validation.user_orders.rating.integer'),
            'rating.between' => __('messages.validation.user_orders.rating.between'),
            'comment.string' => __('messages.validation.user_orders.review_comment.string'),
            'comment.max' => __('messages.validation.user_orders.review_comment.max'),
        ];
    }
}

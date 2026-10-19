<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class CreateUserOrderRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'min:1', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'coupon_code' => ['nullable', 'string', 'max:32'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'product_id' => __('messages.validation.user_orders.product_id.label'),
            'quantity' => __('messages.validation.user_orders.quantity.label'),
            'coupon_code' => __('messages.validation.user_orders.coupon_code.label'),
            'idempotency_key' => __('messages.validation.user_orders.idempotency_key.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'product_id.required' => __('messages.validation.user_orders.product_id.required'),
            'product_id.integer' => __('messages.validation.user_orders.product_id.integer'),
            'product_id.min' => __('messages.validation.user_orders.product_id.min'),
            'product_id.exists' => __('messages.validation.user_orders.product_id.exists'),
            'quantity.required' => __('messages.validation.user_orders.quantity.required'),
            'quantity.integer' => __('messages.validation.user_orders.quantity.integer'),
            'quantity.min' => __('messages.validation.user_orders.quantity.min'),
            'quantity.max' => __('messages.validation.user_orders.quantity.max'),
            'coupon_code.string' => __('messages.validation.user_orders.coupon_code.string'),
            'coupon_code.max' => __('messages.validation.user_orders.coupon_code.max'),
            'idempotency_key.required' => __('messages.validation.user_orders.idempotency_key.required'),
            'idempotency_key.uuid' => __('messages.validation.user_orders.idempotency_key.uuid'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\OrderStatusEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateStoreOrderStatusRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['status' => ['required', 'string', Rule::enum(OrderStatusEnum::class)]];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['status' => __('messages.validation.store_order_status.status.label')];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.required' => __('messages.validation.store_order_status.status.required'),
            'status.string' => __('messages.validation.store_order_status.status.string'),
            'status.enum' => __('messages.validation.store_order_status.status.enum'),
        ];
    }
}

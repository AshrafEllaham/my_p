<?php

namespace App\Http\Requests\Api\Store;

use App\Http\Requests\ApiRequest;

class ConfirmStoreOrderPickupRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['pickup_qr_token' => ['required', 'string', 'uuid']];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['pickup_qr_token' => __('messages.validation.store_pickup.pickup_qr_token.label')];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'pickup_qr_token.required' => __('messages.validation.store_pickup.pickup_qr_token.required'),
            'pickup_qr_token.string' => __('messages.validation.store_pickup.pickup_qr_token.string'),
            'pickup_qr_token.uuid' => __('messages.validation.store_pickup.pickup_qr_token.uuid'),
        ];
    }
}

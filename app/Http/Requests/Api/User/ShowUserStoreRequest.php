<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class ShowUserStoreRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'store' => ['required', 'integer', 'min:1'],
            'lat' => ['nullable', 'required_with:long', 'numeric', 'between:-90,90'],
            'long' => ['nullable', 'required_with:lat', 'numeric', 'between:-180,180'],
        ];
    }

    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return [...parent::validationData(), 'store' => $this->route('store')];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'store' => __('messages.validation.user_store_details.store.label'),
            'lat' => __('messages.validation.nearby_stores.lat.label'),
            'long' => __('messages.validation.nearby_stores.long.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'store.required' => __('messages.validation.user_store_details.store.required'),
            'store.integer' => __('messages.validation.user_store_details.store.integer'),
            'store.min' => __('messages.validation.user_store_details.store.min'),
            'lat.required_with' => __('messages.validation.nearby_stores.lat.required_with'),
            'lat.numeric' => __('messages.validation.nearby_stores.lat.numeric'),
            'lat.between' => __('messages.validation.nearby_stores.lat.between'),
            'long.required_with' => __('messages.validation.nearby_stores.long.required_with'),
            'long.numeric' => __('messages.validation.nearby_stores.long.numeric'),
            'long.between' => __('messages.validation.nearby_stores.long.between'),
        ];
    }
}

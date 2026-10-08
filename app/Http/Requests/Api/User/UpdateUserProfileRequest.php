<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class UpdateUserProfileRequest extends ApiRequest
{


    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => __('messages.validation.profile_name.required'),
            'name.string' => __('messages.validation.profile_name.string'),
            'name.min' => __('messages.validation.profile_name.min'),
            'name.max' => __('messages.validation.profile_name.max'),
            'image.required' => __('messages.validation.profile_image.required'),
            'image.image' => __('messages.validation.profile_image.image'),
            'image.mimes' => __('messages.validation.profile_image.mimes'),
            'image.max' => __('messages.validation.profile_image.max'),
            'address.string' => __('messages.validation.profile_address.string'),
            'address.max' => __('messages.validation.profile_address.max'),
        ];
    }
}

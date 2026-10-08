<?php

namespace App\Http\Requests\Api\Store;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateStoreProfileRequest extends ApiRequest
{

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'cover' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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
            'category_id.required' => __('messages.validation.category_id.required'),
            'category_id.integer' => __('messages.validation.category_id.integer'),
            'category_id.exists' => __('messages.validation.category_id.exists'),
            'description.string' => __('messages.validation.profile_description.string'),
            'description.max' => __('messages.validation.profile_description.max'),
            'cover.image' => __('messages.validation.profile_cover.image'),
            'cover.mimes' => __('messages.validation.profile_cover.mimes'),
            'cover.max' => __('messages.validation.profile_cover.max'),
            'address.string' => __('messages.validation.profile_address.string'),
            'address.max' => __('messages.validation.profile_address.max'),
        ];
    }
}

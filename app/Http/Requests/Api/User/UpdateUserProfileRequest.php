<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $governorateRule = Rule::exists('governorates', 'id');
        if (is_numeric($this->input('country_id'))) {
            $governorateRule->where('country_id', (int) $this->input('country_id'));
        }

        $cityRule = Rule::exists('cities', 'id');
        if (is_numeric($this->input('governorate_id'))) {
            $cityRule->where('governorate_id', (int) $this->input('governorate_id'));
        }

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'country_id' => ['sometimes', 'nullable', 'integer', Rule::exists('countries', 'id')],
            'governorate_id' => ['sometimes', 'nullable', 'integer', $governorateRule],
            'city_id' => ['sometimes', 'nullable', 'integer', $cityRule],
            'pickup_latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
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
            'image.image' => __('messages.validation.profile_image.image'),
            'image.mimes' => __('messages.validation.profile_image.mimes'),
            'image.max' => __('messages.validation.profile_image.max'),
            'address.string' => __('messages.validation.profile_address.string'),
            'address.max' => __('messages.validation.profile_address.max'),
            'country_id.integer' => __('messages.validation.profile_location.integer'),
            'country_id.exists' => __('messages.validation.profile_location.exists'),
            'governorate_id.integer' => __('messages.validation.profile_location.integer'),
            'governorate_id.exists' => __('messages.validation.profile_location.exists'),
            'city_id.integer' => __('messages.validation.profile_location.integer'),
            'city_id.exists' => __('messages.validation.profile_location.exists'),
            'pickup_latitude.numeric' => __('messages.validation.profile_location.latitude'),
            'pickup_latitude.between' => __('messages.validation.profile_location.latitude'),
            'pickup_longitude.numeric' => __('messages.validation.profile_location.longitude'),
            'pickup_longitude.between' => __('messages.validation.profile_location.longitude'),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('messages.validation.profile_name.label'),
            'image' => __('messages.validation.profile_image.label'),
            'address' => __('messages.validation.profile_address.label'),
            'country_id' => __('messages.validation.profile_location.country'),
            'governorate_id' => __('messages.validation.profile_location.governorate'),
            'city_id' => __('messages.validation.profile_location.city'),
            'pickup_latitude' => __('messages.validation.profile_location.latitude_label'),
            'pickup_longitude' => __('messages.validation.profile_location.longitude_label'),
        ];
    }
}

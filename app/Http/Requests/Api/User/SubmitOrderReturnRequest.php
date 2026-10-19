<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class SubmitOrderReturnRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['required', 'string', 'min:10', 'max:2000'],
            'photos' => ['sometimes', 'array', 'max:3'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'reason' => __('messages.validation.user_orders.return_reason.label'),
            'details' => __('messages.validation.user_orders.return_details.label'),
            'photos' => __('messages.validation.user_orders.return_photos.label'),
            'photos.*' => __('messages.validation.user_orders.return_photo.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reason.required' => __('messages.validation.user_orders.return_reason.required'),
            'reason.string' => __('messages.validation.user_orders.return_reason.string'),
            'reason.max' => __('messages.validation.user_orders.return_reason.max'),
            'details.required' => __('messages.validation.user_orders.return_details.required'),
            'details.string' => __('messages.validation.user_orders.return_details.string'),
            'details.min' => __('messages.validation.user_orders.return_details.min'),
            'details.max' => __('messages.validation.user_orders.return_details.max'),
            'photos.array' => __('messages.validation.user_orders.return_photos.array'),
            'photos.max' => __('messages.validation.user_orders.return_photos.max'),
            'photos.*.file' => __('messages.validation.user_orders.return_photo.file'),
            'photos.*.uploaded' => __('messages.validation.user_orders.return_photo.uploaded'),
            'photos.*.image' => __('messages.validation.user_orders.return_photo.image'),
            'photos.*.mimes' => __('messages.validation.user_orders.return_photo.mimes'),
            'photos.*.max' => __('messages.validation.user_orders.return_photo.max'),
        ];
    }
}

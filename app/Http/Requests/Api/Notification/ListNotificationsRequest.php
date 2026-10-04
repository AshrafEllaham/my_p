<?php

namespace App\Http\Requests\Api\Notification;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ListNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'message' => __('messages.validation_failed'),
            'errors' => $validator->errors(),
        ], 422));
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'unread_only' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'unread_only.boolean' => __('messages.validation.notifications_unread_only.boolean'),
            'per_page.integer' => __('messages.validation.notifications_per_page.integer'),
            'per_page.min' => __('messages.validation.notifications_per_page.min'),
            'per_page.max' => __('messages.validation.notifications_per_page.max'),
        ];
    }
}

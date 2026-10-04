<?php

namespace App\Http\Requests\Api\Notification;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateNotificationPreferencesRequest extends FormRequest
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
            'orders_enabled' => ['required', 'boolean'],
            'pickup_enabled' => ['required', 'boolean'],
            'returns_enabled' => ['required', 'boolean'],
            'chats_enabled' => ['required', 'boolean'],
            'offers_enabled' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $required = __('messages.validation.notification_preference.required');
        $boolean = __('messages.validation.notification_preference.boolean');

        return [
            'orders_enabled.required' => $required,
            'orders_enabled.boolean' => $boolean,
            'pickup_enabled.required' => $required,
            'pickup_enabled.boolean' => $boolean,
            'returns_enabled.required' => $required,
            'returns_enabled.boolean' => $boolean,
            'chats_enabled.required' => $required,
            'chats_enabled.boolean' => $boolean,
            'offers_enabled.required' => $required,
            'offers_enabled.boolean' => $boolean,
        ];
    }
}

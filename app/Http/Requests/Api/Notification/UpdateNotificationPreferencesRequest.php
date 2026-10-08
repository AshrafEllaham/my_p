<?php

namespace App\Http\Requests\Api\Notification;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Validator;

class UpdateNotificationPreferencesRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $presence = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'orders_enabled' => [$presence, 'boolean'],
            'pickup_enabled' => [$presence, 'boolean'],
            'returns_enabled' => [$presence, 'boolean'],
            'chats_enabled' => [$presence, 'boolean'],
            'offers_enabled' => [$presence, 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $preferenceFields = [
                'orders_enabled',
                'pickup_enabled',
                'returns_enabled',
                'chats_enabled',
                'offers_enabled',
            ];

            if ($this->isMethod('PATCH') && array_intersect($preferenceFields, array_keys($this->all())) === []) {
                $validator->errors()->add(
                    'preferences',
                    __('messages.validation.notification_preference.at_least_one'),
                );
            }
        }];
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

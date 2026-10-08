<?php

namespace App\Http\Requests\Api\Notification;

use App\Http\Requests\ApiRequest;

class ListNotificationsRequest extends ApiRequest
{

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

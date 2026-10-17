<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class TrackUserAdInteractionRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'string', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'event_id' => __('messages.validation.user_ads.event_id.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'event_id.required' => __('messages.validation.user_ads.event_id.required'),
            'event_id.string' => __('messages.validation.user_ads.event_id.string'),
            'event_id.uuid' => __('messages.validation.user_ads.event_id.uuid'),
        ];
    }
}

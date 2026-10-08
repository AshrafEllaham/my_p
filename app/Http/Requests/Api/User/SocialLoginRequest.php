<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class SocialLoginRequest extends ApiRequest
{

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'social_id' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'social_id.required' => __('messages.validation.social_id.required'),
            'social_id.string' => __('messages.validation.social_id.string'),
            'social_id.max' => __('messages.validation.social_id.max'),
            'email.required' => __('messages.validation.email.required'),
            'email.string' => __('messages.validation.email.string'),
            'email.email' => __('messages.validation.email.email'),
            'email.max' => __('messages.validation.email.max'),
            'name.string' => __('messages.validation.name.string'),
            'name.max' => __('messages.validation.name.max'),
        ];
    }
}

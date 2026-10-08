<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\ApiRequest;

class ChangePasswordRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'different:current_password', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'current_password.required' => __('messages.validation.change_password.current_password_required'),
            'current_password.string' => __('messages.validation.change_password.current_password_string'),
            'password.required' => __('messages.validation.password.required'),
            'password.string' => __('messages.validation.password.string'),
            'password.min' => __('messages.validation.password.min'),
            'password.max' => __('messages.validation.password.max'),
            'password.different' => __('messages.validation.change_password.password_different'),
            'password.confirmed' => __('messages.validation.password.confirmed'),
            'password_confirmation.required' => __('messages.validation.password_confirmation.required'),
            'password_confirmation.string' => __('messages.validation.password_confirmation.string'),
        ];
    }
}

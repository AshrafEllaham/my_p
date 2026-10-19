<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class ConfirmPasswordResetOtpRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'identity' => ['required', 'string', 'min:6', 'max:255'],
            'phone_code' => ['sometimes', 'nullable', 'string', 'regex:/^\\+[1-9][0-9]{0,3}$/', 'max:5'],
            'code' => ['required', 'string', 'digits:4'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'identity' => __('messages.validation.password_reset.identity_label'),
            'phone_code' => __('messages.validation.phone_code.label'),
            'code' => __('messages.validation.otp.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'identity.required' => __('messages.validation.password_reset.identity_required'),
            'identity.string' => __('messages.validation.password_reset.identity_string'),
            'identity.min' => __('messages.validation.password_reset.identity_min'),
            'identity.max' => __('messages.validation.password_reset.identity_max'),
            'phone_code.string' => __('messages.validation.phone_code.string'),
            'phone_code.regex' => __('messages.validation.phone_code.regex'),
            'phone_code.max' => __('messages.validation.phone_code.max'),
            'code.required' => __('messages.validation.otp.required'),
            'code.string' => __('messages.validation.otp.string'),
            'code.digits' => __('messages.validation.otp.digits'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class SendOtpRequest extends ApiRequest
{

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'phone_code' => ['required', 'string', 'regex:/^\+[1-9][0-9]{0,3}$/', 'max:5'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{6,20}$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone_code.required' => __('messages.validation.phone_code.required'),
            'phone_code.string' => __('messages.validation.phone_code.string'),
            'phone_code.regex' => __('messages.validation.phone_code.regex'),
            'phone_code.max' => __('messages.validation.phone_code.max'),
            'phone.required' => __('messages.validation.phone.required'),
            'phone.string' => __('messages.validation.phone.string'),
            'phone.regex' => __('messages.validation.phone.regex'),
        ];
    }
}

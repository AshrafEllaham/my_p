<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends ApiRequest
{

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'phone_code' => ['required', 'string', 'regex:/^\+[1-9][0-9]{0,3}$/', 'max:5'],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{6,20}$/',
                Rule::unique('users', 'phone')->where('phone_code', $this->input('phone_code')),
            ],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
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
            'phone.unique' => __('messages.validation.phone.unique'),
            'email.required' => __('messages.validation.email.required'),
            'email.string' => __('messages.validation.email.string'),
            'email.email' => __('messages.validation.email.email'),
            'email.max' => __('messages.validation.email.max'),
            'email.unique' => __('messages.validation.email.unique'),
            'password.required' => __('messages.validation.password.required'),
            'password.string' => __('messages.validation.password.string'),
            'password.min' => __('messages.validation.password.min'),
            'password.max' => __('messages.validation.password.max'),
            'password.confirmed' => __('messages.validation.password.confirmed'),
            'password_confirmation.required' => __('messages.validation.password_confirmation.required'),
            'password_confirmation.string' => __('messages.validation.password_confirmation.string'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'message' => __('messages.validation_failed'),
            'errors' => $validator->errors(),
        ], 422));
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'email' => ['nullable', 'required_without_all:phone_code,phone', 'string', 'email:rfc', 'max:255', 'prohibits:phone_code,phone'],
            'phone_code' => ['nullable', 'required_with:phone', 'required_without:email', 'string', 'regex:/^\\+[1-9][0-9]{0,3}$/', 'max:5', 'prohibits:email'],
            'phone' => ['nullable', 'required_with:phone_code', 'required_without:email', 'string', 'regex:/^[0-9]{6,20}$/', 'prohibits:email'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required_without_all' => __('messages.validation.email.required'),
            'email.string' => __('messages.validation.email.string'),
            'email.email' => __('messages.validation.email.email'),
            'email.max' => __('messages.validation.email.max'),
            'email.prohibits' => __('messages.validation.login_identity.exclusive'),
            'phone_code.required_with' => __('messages.validation.phone_code.required'),
            'phone_code.required_without' => __('messages.validation.phone_code.required'),
            'phone_code.string' => __('messages.validation.phone_code.string'),
            'phone_code.regex' => __('messages.validation.phone_code.regex'),
            'phone_code.max' => __('messages.validation.phone_code.max'),
            'phone_code.prohibits' => __('messages.validation.login_identity.exclusive'),
            'phone.required_with' => __('messages.validation.phone.required'),
            'phone.required_without' => __('messages.validation.phone.required'),
            'phone.string' => __('messages.validation.phone.string'),
            'phone.regex' => __('messages.validation.phone.regex'),
            'phone.prohibits' => __('messages.validation.login_identity.exclusive'),
            'password.required' => __('messages.validation.password.required'),
            'password.string' => __('messages.validation.password.string'),
            'password.max' => __('messages.validation.password.max'),
        ];
    }
}

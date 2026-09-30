<?php

namespace App\Http\Requests\Admin\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $adminId = $this->user('admin')?->getAuthIdentifier();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('admins', 'email')->ignore($adminId)],
            'phone' => ['nullable', 'string', 'max:32', Rule::unique('admins', 'phone')->ignore($adminId)],
            'current_password' => ['nullable', 'required_with:password', 'current_password:admin'],
            'password' => ['nullable', 'confirmed', Password::min(8), 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => __('admin.profile.validation.name_required'),
            'email.required' => __('admin.profile.validation.email_required'),
            'email.email' => __('admin.profile.validation.email_invalid'),
            'email.unique' => __('admin.profile.validation.email_unique'),
            'phone.unique' => __('admin.profile.validation.phone_unique'),
            'current_password.required_with' => __('admin.profile.validation.current_password_required'),
            'current_password.current_password' => __('admin.profile.validation.current_password_invalid'),
            'password.confirmed' => __('admin.profile.validation.password_confirmation'),
            'password.min' => __('admin.profile.validation.password_min'),
        ];
    }
}

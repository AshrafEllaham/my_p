<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => mb_strtoupper(trim((string) $this->input('code')))]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:2', 'max:3', 'regex:/^[A-Z]+$/', Rule::unique('countries', 'code')->ignore($this->route('country'))],
            'phone_code' => ['nullable', 'string', 'max:10', 'regex:/^\+?[0-9]+$/'],
            'flag' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
            'ar' => ['required', 'array'],
            'ar.name' => ['required', 'string', 'max:255'],
            'en' => ['required', 'array'],
            'en.name' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return __('admin.catalog.validation.attributes');
    }
}

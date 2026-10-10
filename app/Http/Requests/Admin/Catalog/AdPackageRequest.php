<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => mb_strtolower(trim((string) $this->input('code')))]);
        }

        if ($this->has('currency')) {
            $this->merge(['currency' => mb_strtoupper(trim((string) $this->input('currency')))]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'alpha_dash', 'max:100', Rule::unique('ad_packages', 'code')->ignore($this->route('ad_package'))],
            'duration_days' => ['required', 'integer', 'min:1', 'max:65535'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'is_active' => ['required', 'boolean'],
            'ar' => ['required', 'array:name,description'],
            'ar.name' => ['required', 'string', 'max:255'],
            'ar.description' => ['nullable', 'string', 'max:5000'],
            'en' => ['required', 'array:name,description'],
            'en.name' => ['required', 'string', 'max:255'],
            'en.description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return __('admin.ad_packages.validation.attributes');
    }
}

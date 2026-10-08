<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'parent_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_active' => ['required', 'boolean'],
            'ar' => ['required', 'array'],
            'ar.name' => ['required', 'string', 'max:255'],
            'ar.description' => ['nullable', 'string', 'max:5000'],
            'en' => ['required', 'array'],
            'en.name' => ['required', 'string', 'max:255'],
            'en.description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return __('admin.catalog.validation.attributes');
    }
}

<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MainCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->categoryRules($this->route('main_category'));
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return __('admin.catalog.validation.attributes');
    }

    /** @return array<string, mixed> */
    private function categoryRules(mixed $categoryId): array
    {
        return [
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'icon' => ['nullable', 'string', 'max:255'],
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
}

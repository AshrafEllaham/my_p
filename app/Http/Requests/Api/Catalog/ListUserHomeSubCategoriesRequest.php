<?php

namespace App\Http\Requests\Api\Catalog;

use Illuminate\Validation\Rule;

class ListUserHomeSubCategoriesRequest extends ListSubCategoriesRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'page' => ['sometimes', 'integer', 'min:1'],
            'main_category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->whereNull('parent_id')
                    ->where('is_active', true)),
            ],
        ]);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return parent::attributes() + [
            'page' => __('messages.validation.catalog_lookup.page.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'main_category_id.required' => __('messages.validation.catalog_lookup.main_category_id.required'),
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
        ];
    }
}

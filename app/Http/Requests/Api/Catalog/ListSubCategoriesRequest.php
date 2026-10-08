<?php

namespace App\Http\Requests\Api\Catalog;

use Illuminate\Validation\Rule;

class ListSubCategoriesRequest extends LookupListRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->paginationRules() + [
            'main_category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNull('parent_id')->where('is_active', true)),
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return parent::attributes() + [
            'main_category_id' => __('messages.validation.catalog_lookup.main_category_id.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'main_category_id.integer' => __('messages.validation.catalog_lookup.main_category_id.integer'),
            'main_category_id.exists' => __('messages.validation.catalog_lookup.main_category_id.exists'),
        ];
    }
}

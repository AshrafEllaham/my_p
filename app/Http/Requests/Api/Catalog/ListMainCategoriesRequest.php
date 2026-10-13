<?php

namespace App\Http\Requests\Api\Catalog;

class ListMainCategoriesRequest extends LookupListRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            ...$this->paginationRules(),
            'page' => ['sometimes', 'integer', 'min:1'],
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'page' => __('messages.validation.catalog_lookup.page.label'),
            'search' => __('messages.validation.catalog_lookup.search.label'),
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
            'search.string' => __('messages.validation.catalog_lookup.search.string'),
            'search.max' => __('messages.validation.catalog_lookup.search.max'),
        ];
    }
}

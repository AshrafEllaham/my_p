<?php

namespace App\Http\Requests\Api\Catalog;

use App\Http\Requests\ApiRequest;

abstract class LookupListRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    protected function paginationRules(): array
    {
        return [
            'pagination' => ['sometimes', 'string', 'in:on,off'],
            'limit_per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'pagination' => __('messages.validation.catalog_lookup.pagination.label'),
            'limit_per_page' => __('messages.validation.catalog_lookup.limit_per_page.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'pagination.string' => __('messages.validation.catalog_lookup.pagination.string'),
            'pagination.in' => __('messages.validation.catalog_lookup.pagination.in'),
            'limit_per_page.integer' => __('messages.validation.catalog_lookup.limit_per_page.integer'),
            'limit_per_page.min' => __('messages.validation.catalog_lookup.limit_per_page.min'),
            'limit_per_page.max' => __('messages.validation.catalog_lookup.limit_per_page.max'),
        ];
    }
}

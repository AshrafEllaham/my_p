<?php

namespace App\Http\Requests\Api\Catalog;

use Illuminate\Validation\Rule;

class ListGovernoratesRequest extends LookupListRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->paginationRules() + [
            'country_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('countries', 'id')->where('is_active', true),
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return parent::attributes() + [
            'country_id' => __('messages.validation.catalog_lookup.country_id.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'country_id.integer' => __('messages.validation.catalog_lookup.country_id.integer'),
            'country_id.exists' => __('messages.validation.catalog_lookup.country_id.exists'),
        ];
    }
}

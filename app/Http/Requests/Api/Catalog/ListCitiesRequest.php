<?php

namespace App\Http\Requests\Api\Catalog;

use Illuminate\Validation\Rule;

class ListCitiesRequest extends LookupListRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->paginationRules() + [
            'governorate_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('governorates', 'id')->where('is_active', true),
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return parent::attributes() + [
            'governorate_id' => __('messages.validation.catalog_lookup.governorate_id.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'governorate_id.integer' => __('messages.validation.catalog_lookup.governorate_id.integer'),
            'governorate_id.exists' => __('messages.validation.catalog_lookup.governorate_id.exists'),
        ];
    }
}

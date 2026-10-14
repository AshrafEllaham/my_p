<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\Catalog\LookupListRequest;

class ListUserStoreAdsRequest extends LookupListRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge(['pagination' => 'on']);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            ...$this->paginationRules(),
            'store' => ['required', 'integer', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return [...parent::validationData(), 'store' => $this->route('store')];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'store' => __('messages.validation.user_store_details.store.label'),
            'page' => __('messages.validation.catalog_lookup.page.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'store.required' => __('messages.validation.user_store_details.store.required'),
            'store.integer' => __('messages.validation.user_store_details.store.integer'),
            'store.min' => __('messages.validation.user_store_details.store.min'),
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
        ];
    }
}

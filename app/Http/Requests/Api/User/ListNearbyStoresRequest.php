<?php

namespace App\Http\Requests\Api\User;

use App\Enums\UserStoreSortEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListNearbyStoresRequest extends LookupListRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'main_category_id' => [
                'sometimes',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true)->whereNull('parent_id'),
            ],
            'governorate_id' => ['sometimes', 'integer', Rule::exists('governorates', 'id')->where('is_active', true)],
            'sort' => ['sometimes', 'string', Rule::enum(UserStoreSortEnum::class)],
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'lat' => ['nullable', 'required_with:long', 'required_if:sort,nearest', 'numeric', 'between:-90,90'],
            'long' => ['nullable', 'required_with:lat', 'required_if:sort,nearest', 'numeric', 'between:-180,180'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'page' => __('messages.validation.catalog_lookup.page.label'),
            'main_category_id' => __('messages.validation.user_store_filters.main_category_id.label'),
            'governorate_id' => __('messages.validation.user_store_filters.governorate_id.label'),
            'sort' => __('messages.validation.user_store_filters.sort.label'),
            'search' => __('messages.validation.user_store_filters.search.label'),
            'lat' => __('messages.validation.nearby_stores.lat.label'),
            'long' => __('messages.validation.nearby_stores.long.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
            'main_category_id.integer' => __('messages.validation.user_store_filters.main_category_id.integer'),
            'main_category_id.exists' => __('messages.validation.user_store_filters.main_category_id.exists'),
            'governorate_id.integer' => __('messages.validation.user_store_filters.governorate_id.integer'),
            'governorate_id.exists' => __('messages.validation.user_store_filters.governorate_id.exists'),
            'sort.string' => __('messages.validation.user_store_filters.sort.string'),
            'sort.enum' => __('messages.validation.user_store_filters.sort.enum'),
            'search.string' => __('messages.validation.user_store_filters.search.string'),
            'search.max' => __('messages.validation.user_store_filters.search.max'),
            'lat.required_with' => __('messages.validation.nearby_stores.lat.required_with'),
            'lat.required_if' => __('messages.validation.nearby_stores.lat.required_if'),
            'lat.numeric' => __('messages.validation.nearby_stores.lat.numeric'),
            'lat.between' => __('messages.validation.nearby_stores.lat.between'),
            'long.required_with' => __('messages.validation.nearby_stores.long.required_with'),
            'long.required_if' => __('messages.validation.nearby_stores.long.required_if'),
            'long.numeric' => __('messages.validation.nearby_stores.long.numeric'),
            'long.between' => __('messages.validation.nearby_stores.long.between'),
        ];
    }
}

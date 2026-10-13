<?php

namespace App\Http\Requests\Api\User;

use App\Enums\UserProductSortEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListUserProductsRequest extends LookupListRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $mainCategoryId = $this->input('main_category_id');

        $subCategoryRule = Rule::exists('categories', 'id')
            ->where('is_active', true)
            ->whereNotNull('parent_id');

        if (is_numeric($mainCategoryId)) {
            $subCategoryRule->where('parent_id', (int) $mainCategoryId);
        }

        return [
            ...$this->paginationRules(),
            'page' => ['sometimes', 'integer', 'min:1'],
            'main_category_id' => [
                'sometimes',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true)->whereNull('parent_id'),
            ],
            'sub_category_id' => ['sometimes', 'integer', $subCategoryRule],
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
            'sort' => ['sometimes', 'string', Rule::enum(UserProductSortEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'page' => __('messages.validation.catalog_lookup.page.label'),
            'main_category_id' => __('messages.validation.user_product_filters.main_category_id.label'),
            'sub_category_id' => __('messages.validation.user_product_filters.sub_category_id.label'),
            'search' => __('messages.validation.user_product_filters.search.label'),
            'sort' => __('messages.validation.user_product_filters.sort.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
            'main_category_id.integer' => __('messages.validation.user_product_filters.main_category_id.integer'),
            'main_category_id.exists' => __('messages.validation.user_product_filters.main_category_id.exists'),
            'sub_category_id.integer' => __('messages.validation.user_product_filters.sub_category_id.integer'),
            'sub_category_id.exists' => __('messages.validation.user_product_filters.sub_category_id.exists'),
            'search.string' => __('messages.validation.user_product_filters.search.string'),
            'search.max' => __('messages.validation.user_product_filters.search.max'),
            'sort.string' => __('messages.validation.user_product_filters.sort.string'),
            'sort.enum' => __('messages.validation.user_product_filters.sort.enum'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\ProductOrderByEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ProductStockFilterEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListStoreProductsRequest extends LookupListRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge(['pagination' => 'on']);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->paginationRules() + [
            'page' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', Rule::enum(ProductStatusEnum::class)],
            'category_id' => [
                'sometimes',
                'integer',
                Rule::exists('categories', 'id')->whereNotNull('parent_id')->where('is_active', true),
            ],
            'order_by' => ['sometimes', 'string', Rule::enum(ProductOrderByEnum::class)],
            'search' => ['sometimes', 'string', 'max:255'],
            'stock_status' => ['sometimes', 'string', Rule::enum(ProductStockFilterEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return parent::attributes() + [
            'page' => __('messages.validation.catalog_lookup.page.label'),
            'status' => __('messages.validation.store_product_filters.status.label'),
            'category_id' => __('messages.validation.store_product_filters.category_id.label'),
            'order_by' => __('messages.validation.store_product_filters.order_by.label'),
            'search' => __('messages.validation.store_product_filters.search.label'),
            'stock_status' => __('messages.validation.store_product_filters.stock_status.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
            'status.string' => __('messages.validation.store_product_filters.status.string'),
            'status.enum' => __('messages.validation.store_product_filters.status.enum'),
            'category_id.integer' => __('messages.validation.store_product_filters.category_id.integer'),
            'category_id.exists' => __('messages.validation.store_product_filters.category_id.exists'),
            'order_by.string' => __('messages.validation.store_product_filters.order_by.string'),
            'order_by.enum' => __('messages.validation.store_product_filters.order_by.enum'),
            'search.string' => __('messages.validation.store_product_filters.search.string'),
            'search.max' => __('messages.validation.store_product_filters.search.max'),
            'stock_status.string' => __('messages.validation.store_product_filters.stock_status.string'),
            'stock_status.enum' => __('messages.validation.store_product_filters.stock_status.enum'),
        ];
    }
}

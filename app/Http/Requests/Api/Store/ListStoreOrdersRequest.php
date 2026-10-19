<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\OrderStatusEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListStoreOrdersRequest extends LookupListRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if (! $this->has('pagination')) {
            $this->merge(['pagination' => 'on']);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->paginationRules() + [
            'page' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', Rule::enum(OrderStatusEnum::class)],
            'search' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return parent::attributes() + [
            'page' => __('messages.validation.store_order_filters.page.label'),
            'status' => __('messages.validation.store_order_filters.status.label'),
            'search' => __('messages.validation.store_order_filters.search.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'page.integer' => __('messages.validation.store_order_filters.page.integer'),
            'page.min' => __('messages.validation.store_order_filters.page.min'),
            'status.string' => __('messages.validation.store_order_filters.status.string'),
            'status.enum' => __('messages.validation.store_order_filters.status.enum'),
            'search.string' => __('messages.validation.store_order_filters.search.string'),
            'search.max' => __('messages.validation.store_order_filters.search.max'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\StoreReviewPeriodEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListStoreReviewsRequest extends LookupListRequest
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
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'period' => ['sometimes', 'string', Rule::enum(StoreReviewPeriodEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return parent::attributes() + [
            'page' => __('messages.validation.catalog_lookup.page.label'),
            'rating' => __('messages.validation.store_review_filters.rating.label'),
            'period' => __('messages.validation.store_review_filters.period.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
            'rating.integer' => __('messages.validation.store_review_filters.rating.integer'),
            'rating.between' => __('messages.validation.store_review_filters.rating.between'),
            'period.string' => __('messages.validation.store_review_filters.period.string'),
            'period.enum' => __('messages.validation.store_review_filters.period.enum'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\User;

use App\Enums\AdPlacementEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListUserAdsRequest extends LookupListRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge([
            'pagination' => 'on',
            'limit_per_page' => $this->input('limit_per_page', 5),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            ...$this->paginationRules(),
            'placement' => [
                'required',
                'string',
                Rule::enum(AdPlacementEnum::class)->except(AdPlacementEnum::Storefront),
            ],
            'category_id' => [
                Rule::requiredIf(fn () => $this->input('placement') === AdPlacementEnum::Category->value),
                Rule::prohibitedIf(fn () => $this->input('placement') !== AdPlacementEnum::Category->value),
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'placement' => __('messages.validation.user_ads.placement.label'),
            'category_id' => __('messages.validation.user_ads.category_id.label'),
            'page' => __('messages.validation.catalog_lookup.page.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'placement.required' => __('messages.validation.user_ads.placement.required'),
            'placement.string' => __('messages.validation.user_ads.placement.string'),
            'placement.enum' => __('messages.validation.user_ads.placement.enum'),
            'category_id.required' => __('messages.validation.user_ads.category_id.required'),
            'category_id.prohibited' => __('messages.validation.user_ads.category_id.prohibited'),
            'category_id.integer' => __('messages.validation.user_ads.category_id.integer'),
            'category_id.exists' => __('messages.validation.user_ads.category_id.exists'),
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
        ];
    }
}

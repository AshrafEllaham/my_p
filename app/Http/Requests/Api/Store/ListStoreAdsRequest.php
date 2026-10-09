<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListStoreAdsRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['pagination' => 'on']);
    }

    public function rules(): array
    {
        return [
            'pagination' => ['sometimes', 'in:on'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit_per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', Rule::enum(AdStatusEnum::class)],
            'placement' => ['sometimes', 'string', Rule::enum(AdPlacementEnum::class)],
            'search' => ['sometimes', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'page' => __('messages.validation.store_ads.page.label'),
            'limit_per_page' => __('messages.validation.store_ads.limit_per_page.label'),
            'status' => __('messages.validation.store_ads.status.label'),
            'placement' => __('messages.validation.store_ads.placement.label'),
            'search' => __('messages.validation.store_ads.search.label'),
        ];
    }

    public function messages(): array
    {
        $messages = [];
        foreach (['page', 'limit_per_page', 'status', 'placement', 'search'] as $field) {
            foreach (['integer', 'min', 'max', 'string', 'enum'] as $rule) {
                $key = "messages.validation.store_ads.{$field}.{$rule}";
                if (__($key) !== $key) {
                    $messages["{$field}.{$rule}"] = __($key);
                }
            }
        }

        return $messages;
    }
}

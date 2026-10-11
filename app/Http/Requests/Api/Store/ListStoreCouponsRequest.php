<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\CouponListStatusEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListStoreCouponsRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('pagination')) {
            $this->merge(['pagination' => 'off']);
        }
    }

    public function rules(): array
    {
        return [
            'pagination' => ['sometimes', 'in:on,off'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit_per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', Rule::enum(CouponListStatusEnum::class)],
            'search' => ['sometimes', 'string', 'max:32'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pagination' => __('messages.validation.store_coupons.pagination.label'),
            'page' => __('messages.validation.store_coupons.page.label'),
            'limit_per_page' => __('messages.validation.store_coupons.limit_per_page.label'),
            'status' => __('messages.validation.store_coupons.status.label'),
            'search' => __('messages.validation.store_coupons.search.label'),
        ];
    }

    public function messages(): array
    {
        $messages = [];
        foreach (['pagination', 'page', 'limit_per_page', 'status', 'search'] as $field) {
            foreach (['integer', 'min', 'max', 'string', 'enum', 'in'] as $rule) {
                $key = "messages.validation.store_coupons.{$field}.{$rule}";
                if (__($key) !== $key) {
                    $messages["{$field}.{$rule}"] = __($key);
                }
            }
        }

        return $messages;
    }
}

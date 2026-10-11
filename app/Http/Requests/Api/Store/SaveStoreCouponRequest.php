<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\CouponDiscountTypeEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaveStoreCouponRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $ownerId = auth('api')->id();
        $storeIds = DB::table('stores')->select('id')->where('owner_id', $ownerId);
        $couponId = $this->route('coupon');

        return [
            'code' => [
                $isCreate ? 'required' : 'sometimes', 'string', 'min:3', 'max:20', 'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons', 'code')->where(fn (Builder $query) => $query->whereIn('store_id', $storeIds))->ignore($couponId),
            ],
            'discount_type' => [$isCreate ? 'required' : 'sometimes', 'string', Rule::enum(CouponDiscountTypeEnum::class)],
            'discount_value' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'gt:0', 'max:9999999999.99', ...($this->input('discount_type') === CouponDiscountTypeEnum::Percentage->value ? ['lte:100'] : [])],
            'minimum_order_amount' => [$isCreate ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:9999999999.99'],
            'maximum_discount_amount' => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
            'max_uses' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:1', 'max:4294967295'],
            'max_uses_per_user' => [$isCreate ? 'required' : 'sometimes', 'integer', 'min:1', 'max:4294967295', ...($this->filled('max_uses') ? ['lte:max_uses'] : [])],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'expires_at' => [$isCreate ? 'required' : 'sometimes', 'date', 'after:now'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        $fields = ['code', 'discount_type', 'discount_value', 'minimum_order_amount', 'maximum_discount_amount', 'max_uses', 'max_uses_per_user', 'starts_at', 'expires_at', 'is_active'];
        $labels = [];
        foreach ($fields as $field) {
            $labels[$field] = __("messages.validation.store_coupons.{$field}.label");
        }

        return $labels;
    }

    public function messages(): array
    {
        $messages = [];
        $rules = ['required', 'string', 'min', 'max', 'regex', 'unique', 'enum', 'numeric', 'gt', 'min', 'integer', 'lte', 'date', 'after', 'boolean'];
        foreach (['code', 'discount_type', 'discount_value', 'minimum_order_amount', 'maximum_discount_amount', 'max_uses', 'max_uses_per_user', 'starts_at', 'expires_at', 'is_active'] as $field) {
            foreach ($rules as $rule) {
                $key = "messages.validation.store_coupons.{$field}.{$rule}";
                if (__($key) !== $key) {
                    $messages["{$field}.{$rule}"] = __($key);
                }
            }
        }

        return $messages;
    }
}

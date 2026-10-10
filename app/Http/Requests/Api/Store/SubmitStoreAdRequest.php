<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\PaymentMethodEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class SubmitStoreAdRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethodEnum::class)],
        ];
    }

    public function attributes(): array
    {
        return ['payment_method' => __('messages.validation.store_ads.payment_method.label')];
    }

    public function messages(): array
    {
        $messages = [];
        foreach (['required', 'string', 'enum'] as $rule) {
            $key = "messages.validation.store_ads.payment_method.{$rule}";
            if (__($key) !== $key) {
                $messages["payment_method.{$rule}"] = __($key);
            }
        }

        return $messages;
    }
}

<?php

namespace App\Http\Requests\Api\Wallet;

use App\Http\Requests\ApiRequest;

class TopUpWalletRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:999999999999.99'],
            'idempotency_key' => ['required', 'string', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => __('messages.validation.wallet_top_up.amount.label'),
            'idempotency_key' => __('messages.validation.wallet_top_up.idempotency_key.label'),
        ];
    }

    public function messages(): array
    {
        $messages = [];

        foreach (['amount', 'idempotency_key'] as $field) {
            foreach (['required', 'numeric', 'gt', 'decimal', 'max', 'string', 'uuid'] as $rule) {
                $key = "messages.validation.wallet_top_up.{$field}.{$rule}";
                if (__($key) !== $key) {
                    $messages["{$field}.{$rule}"] = __($key);
                }
            }
        }

        return $messages;
    }
}

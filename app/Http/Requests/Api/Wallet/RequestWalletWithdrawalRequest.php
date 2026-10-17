<?php

namespace App\Http\Requests\Api\Wallet;

use App\Enums\PayoutMethodEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class RequestWalletWithdrawalRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:999999999999.99'],
            'method' => ['required', 'string', Rule::enum(PayoutMethodEnum::class)],
            'destination' => ['required', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'amount' => __('messages.validation.wallet_withdrawal.amount.label'),
            'method' => __('messages.validation.wallet_withdrawal.method.label'),
            'destination' => __('messages.validation.wallet_withdrawal.destination.label'),
            'idempotency_key' => __('messages.validation.wallet_withdrawal.idempotency_key.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'amount.required' => __('messages.validation.wallet_withdrawal.amount.required'),
            'amount.numeric' => __('messages.validation.wallet_withdrawal.amount.numeric'),
            'amount.gt' => __('messages.validation.wallet_withdrawal.amount.gt'),
            'amount.decimal' => __('messages.validation.wallet_withdrawal.amount.decimal'),
            'amount.max' => __('messages.validation.wallet_withdrawal.amount.max'),
            'method.required' => __('messages.validation.wallet_withdrawal.method.required'),
            'method.string' => __('messages.validation.wallet_withdrawal.method.string'),
            'method.enum' => __('messages.validation.wallet_withdrawal.method.enum'),
            'destination.required' => __('messages.validation.wallet_withdrawal.destination.required'),
            'destination.string' => __('messages.validation.wallet_withdrawal.destination.string'),
            'destination.max' => __('messages.validation.wallet_withdrawal.destination.max'),
            'idempotency_key.required' => __('messages.validation.wallet_withdrawal.idempotency_key.required'),
            'idempotency_key.string' => __('messages.validation.wallet_withdrawal.idempotency_key.string'),
            'idempotency_key.uuid' => __('messages.validation.wallet_withdrawal.idempotency_key.uuid'),
        ];
    }
}

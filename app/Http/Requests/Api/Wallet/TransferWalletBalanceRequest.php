<?php

namespace App\Http\Requests\Api\Wallet;

use App\Http\Requests\ApiRequest;

class TransferWalletBalanceRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'max:255', 'regex:/^(?:[^\\s@]+@[^\\s@]+\\.[^\\s@]+|\\+?[0-9][0-9\\s().-]{5,30})$/D'],
            'amount' => ['required', 'numeric', 'min:1', 'decimal:0,2', 'max:999999999999.99'],
            'idempotency_key' => ['required', 'string', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'recipient' => __('messages.validation.wallet_transfer.recipient.label'),
            'amount' => __('messages.validation.wallet_transfer.amount.label'),
            'idempotency_key' => __('messages.validation.wallet_transfer.idempotency_key.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'recipient.required' => __('messages.validation.wallet_transfer.recipient.required'),
            'recipient.string' => __('messages.validation.wallet_transfer.recipient.string'),
            'recipient.max' => __('messages.validation.wallet_transfer.recipient.max'),
            'recipient.regex' => __('messages.validation.wallet_transfer.recipient.regex'),
            'amount.required' => __('messages.validation.wallet_transfer.amount.required'),
            'amount.numeric' => __('messages.validation.wallet_transfer.amount.numeric'),
            'amount.min' => __('messages.validation.wallet_transfer.amount.min'),
            'amount.decimal' => __('messages.validation.wallet_transfer.amount.decimal'),
            'amount.max' => __('messages.validation.wallet_transfer.amount.max'),
            'idempotency_key.required' => __('messages.validation.wallet_transfer.idempotency_key.required'),
            'idempotency_key.string' => __('messages.validation.wallet_transfer.idempotency_key.string'),
            'idempotency_key.uuid' => __('messages.validation.wallet_transfer.idempotency_key.uuid'),
        ];
    }
}

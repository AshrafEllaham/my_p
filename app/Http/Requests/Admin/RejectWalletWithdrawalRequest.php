<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectWalletWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'reason' => __('admin.wallet_withdrawals.fields.rejection_reason'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reason.required' => __('admin.wallet_withdrawals.validation.reason_required'),
            'reason.max' => __('admin.wallet_withdrawals.validation.reason_max'),
        ];
    }
}

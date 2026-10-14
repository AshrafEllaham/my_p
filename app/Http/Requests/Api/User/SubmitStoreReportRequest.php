<?php

namespace App\Http\Requests\Api\User;

use App\Enums\UserReportTypeEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class SubmitStoreReportRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'store' => ['required', 'integer', 'min:1'],
            'report_reason_id' => [
                'required',
                'integer',
                Rule::exists('report_reasons', 'id')->where('type', UserReportTypeEnum::Store->value),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return [...parent::validationData(), 'store' => $this->route('store')];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'store' => __('messages.validation.user_store_details.store.label'),
            'report_reason_id' => __('messages.validation.user_reports.reason.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'store.required' => __('messages.validation.user_store_details.store.required'),
            'store.integer' => __('messages.validation.user_store_details.store.integer'),
            'store.min' => __('messages.validation.user_store_details.store.min'),
            'report_reason_id.required' => __('messages.validation.user_reports.reason.required'),
            'report_reason_id.integer' => __('messages.validation.user_reports.reason.integer'),
            'report_reason_id.exists' => __('messages.validation.user_reports.reason.exists'),
        ];
    }
}

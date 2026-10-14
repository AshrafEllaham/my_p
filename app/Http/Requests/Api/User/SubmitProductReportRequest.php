<?php

namespace App\Http\Requests\Api\User;

use App\Enums\UserReportTypeEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class SubmitProductReportRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'product' => ['required', 'integer', 'min:1'],
            'report_reason_id' => [
                'required',
                'integer',
                Rule::exists('report_reasons', 'id')->where('type', UserReportTypeEnum::Product->value),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return [...parent::validationData(), 'product' => $this->route('product')];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'product' => __('messages.validation.user_product_details.product.label'),
            'report_reason_id' => __('messages.validation.user_reports.reason.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'product.required' => __('messages.validation.user_product_details.product.required'),
            'product.integer' => __('messages.validation.user_product_details.product.integer'),
            'product.min' => __('messages.validation.user_product_details.product.min'),
            'report_reason_id.required' => __('messages.validation.user_reports.reason.required'),
            'report_reason_id.integer' => __('messages.validation.user_reports.reason.integer'),
            'report_reason_id.exists' => __('messages.validation.user_reports.reason.exists'),
        ];
    }
}

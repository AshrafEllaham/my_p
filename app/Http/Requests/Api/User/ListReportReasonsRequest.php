<?php

namespace App\Http\Requests\Api\User;

use App\Enums\UserReportTypeEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListReportReasonsRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(UserReportTypeEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['type' => __('messages.validation.user_reports.type.label')];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'type.required' => __('messages.validation.user_reports.type.required'),
            'type.enum' => __('messages.validation.user_reports.type.enum'),
        ];
    }
}

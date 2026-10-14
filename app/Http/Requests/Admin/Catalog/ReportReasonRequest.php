<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Enums\UserReportTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportReasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(UserReportTypeEnum::class)],
            'ar' => ['required', 'array:title'],
            'ar.title' => ['required', 'string', 'max:255'],
            'en' => ['required', 'array:title'],
            'en.title' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return __('admin.report_reasons.validation.attributes');
    }
}

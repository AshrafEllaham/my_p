<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Enums\AccountTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AccountTypeEnum::class)],
            'ar' => ['required', 'array:question,answer'],
            'ar.question' => ['required', 'string', 'max:255'],
            'ar.answer' => ['required', 'string'],
            'en' => ['required', 'array:question,answer'],
            'en.question' => ['required', 'string', 'max:255'],
            'en.answer' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return __('admin.faqs.validation.attributes');
    }
}

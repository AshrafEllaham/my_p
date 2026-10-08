<?php

namespace App\Http\Requests\Admin;

use App\Enums\AccountTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $fileRules = $this->route('banner')
            ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']
            : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'];

        return [
            'file' => $fileRules,
            'type' => ['required', Rule::enum(AccountTypeEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return __('admin.banners.validation.attributes');
    }
}

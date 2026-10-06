<?php

namespace App\Http\Requests\Admin;

use App\Enums\DeveloperCommandEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RunDeveloperCommandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'command' => ['required', 'string', Rule::enum(DeveloperCommandEnum::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'command' => __('admin.developer_tools.command'),
        ];
    }
}

<?php

namespace App\Http\Requests\Admin\Developer;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDeveloperCommandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('command')) {
            $this->merge([
                'command' => preg_replace('/\s+/', ' ', trim((string) $this->input('command'))),
            ]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $commandId = $this->route('command');

        return [
            'command' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! preg_match('~^php artisan [a-zA-Z0-9:_-]+(?: [a-zA-Z0-9_./:=,@+\\\\-]+)*$~', $value)) {
                        $fail(__('messages.validation.developer_command.format'));
                    }
                },
                Rule::unique('commands', 'command')->ignore($commandId),
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'command' => __('admin.developer_tools.command'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'command.unique' => __('messages.validation.developer_command.duplicate'),
        ];
    }
}

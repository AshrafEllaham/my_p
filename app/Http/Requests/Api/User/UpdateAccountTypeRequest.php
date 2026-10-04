<?php

namespace App\Http\Requests\Api\User;

use App\Enums\AccountTypeEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateAccountTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'message' => __('messages.validation_failed'),
            'errors' => $validator->errors(),
        ], 422));
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'account_type' => ['required', Rule::enum(AccountTypeEnum::class)],
            'country_id' => [
                'sometimes',
                'nullable',
                'required_with:governorate_id,city_id',
                'integer',
                Rule::exists('countries', 'id')->where('is_active', true),
            ],
            'governorate_id' => [
                'sometimes',
                'nullable',
                'required_with:city_id',
                'integer',
                Rule::exists('governorates', 'id')->where(function ($query): void {
                    $query->where('is_active', true);

                    if ($this->filled('country_id')) {
                        $query->where('country_id', $this->integer('country_id'));
                    }
                }),
            ],
            'city_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('cities', 'id')->where(function ($query): void {
                    $query->where('is_active', true);

                    if ($this->filled('governorate_id')) {
                        $query->where('governorate_id', $this->integer('governorate_id'));
                    }
                }),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'account_type.required' => __('messages.validation.account_type.required'),
            'account_type.enum' => __('messages.validation.account_type.enum'),
            'country_id.required_with' => __('messages.validation.country_id.required_with'),
            'country_id.integer' => __('messages.validation.country_id.integer'),
            'country_id.exists' => __('messages.validation.country_id.exists'),
            'governorate_id.required_with' => __('messages.validation.governorate_id.required_with'),
            'governorate_id.integer' => __('messages.validation.governorate_id.integer'),
            'governorate_id.exists' => __('messages.validation.governorate_id.exists'),
            'city_id.integer' => __('messages.validation.city_id.integer'),
            'city_id.exists' => __('messages.validation.city_id.exists'),
        ];
    }
}

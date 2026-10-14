<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class ShowUserProductRequest extends ApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['product' => ['required', 'integer', 'min:1']];
    }

    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return [...parent::validationData(), 'product' => $this->route('product')];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['product' => __('messages.validation.user_product_details.product.label')];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'product.required' => __('messages.validation.user_product_details.product.required'),
            'product.integer' => __('messages.validation.user_product_details.product.integer'),
            'product.min' => __('messages.validation.user_product_details.product.min'),
        ];
    }
}

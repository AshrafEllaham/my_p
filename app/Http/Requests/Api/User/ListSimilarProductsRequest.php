<?php

namespace App\Http\Requests\Api\User;

class ListSimilarProductsRequest extends ListUserProductsRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge(['pagination' => 'on']);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return parent::rules() + ['product' => ['required', 'integer', 'min:1']];
    }

    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return [...parent::validationData(), 'product' => $this->route('product')];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return parent::attributes() + ['product' => __('messages.validation.user_product_details.product.label')];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'product.required' => __('messages.validation.user_product_details.product.required'),
            'product.integer' => __('messages.validation.user_product_details.product.integer'),
            'product.min' => __('messages.validation.user_product_details.product.min'),
        ];
    }
}

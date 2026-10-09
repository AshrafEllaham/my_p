<?php

namespace App\Http\Requests\Api\Store;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'category_id' => ['sometimes', 'required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'description' => ['sometimes', 'required', 'string', 'max:300'],
            'features' => ['sometimes', 'array', 'max:8'],
            'features.*' => ['required', 'string', 'max:80'],
            'price' => ['sometimes', 'required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0', 'max:4294967295'],
            'discount_percentage' => ['sometimes', 'nullable', 'numeric', 'between:1,90'],
            'discount_ends_at' => ['sometimes', 'nullable', 'date', 'after:now', 'required_with:discount_percentage'],
            'images' => ['sometimes', 'array', 'max:6'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'publish' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $messages = [];
        foreach (['name', 'category_id', 'description', 'features', 'features.*', 'price', 'stock_quantity', 'discount_percentage', 'discount_ends_at', 'images', 'images.*', 'publish'] as $field) {
            foreach (['required', 'required_with', 'string', 'min', 'max', 'integer', 'numeric', 'gt', 'between', 'date', 'after', 'array', 'image', 'mimes', 'exists', 'boolean', 'uploaded'] as $rule) {
                $key = "messages.validation.product.{$field}.{$rule}";
                if (__($key) !== $key) {
                    $messages["{$field}.{$rule}"] = __($key);
                }
            }
        }

        return $messages;
    }
}

<?php

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\ApiRequest;

class ListChatRoomsRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'pagination' => 'on',
            'limit_per_page' => $this->input('limit_per_page', 20),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit_per_page' => ['required', 'integer', 'min:1', 'max:50'],
            'search' => ['sometimes', 'nullable', 'string', 'max:120'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'page' => __('messages.validation.chat.page.label'),
            'limit_per_page' => __('messages.validation.chat.per_page.label'),
            'search' => __('messages.validation.chat.search.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'page.integer' => __('messages.validation.chat.page.integer'),
            'page.min' => __('messages.validation.chat.page.min'),
            'limit_per_page.required' => __('messages.validation.chat.per_page.required'),
            'limit_per_page.integer' => __('messages.validation.chat.per_page.integer'),
            'limit_per_page.min' => __('messages.validation.chat.per_page.min'),
            'limit_per_page.max' => __('messages.validation.chat.per_page.max'),
            'search.string' => __('messages.validation.chat.search.string'),
            'search.max' => __('messages.validation.chat.search.max'),
        ];
    }
}

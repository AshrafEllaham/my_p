<?php

namespace App\Http\Requests\Api\Store;

use App\Http\Requests\ApiRequest;

class SaveStoreCompetitionRequest extends ApiRequest
{
    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'min:2', 'max:120'],
            'question' => [$isCreate ? 'required' : 'sometimes', 'string', 'min:3', 'max:5000'],
            'prize' => [$isCreate ? 'required' : 'sometimes', 'string', 'min:1', 'max:160'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'ends_at' => [$isCreate ? 'required' : 'sometimes', 'date', 'after:now'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => __('messages.validation.store_competitions.title.label'),
            'question' => __('messages.validation.store_competitions.question.label'),
            'prize' => __('messages.validation.store_competitions.prize.label'),
            'image' => __('messages.validation.store_competitions.image.label'),
            'ends_at' => __('messages.validation.store_competitions.ends_at.label'),
        ];
    }

    public function messages(): array
    {
        $messages = [];
        foreach (['title', 'question', 'prize', 'image', 'ends_at'] as $field) {
            foreach (['required', 'string', 'min', 'max', 'image', 'mimes', 'date', 'after'] as $rule) {
                $key = "messages.validation.store_competitions.{$field}.{$rule}";
                if (__($key) !== $key) {
                    $messages["{$field}.{$rule}"] = __($key);
                }
            }
        }

        return $messages;
    }
}

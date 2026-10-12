<?php

namespace App\Http\Requests\Api\Store;

use App\Http\Requests\ApiRequest;

class DrawStoreCompetitionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'correct_answer' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'correct_answer' => __('messages.validation.store_competitions.correct_answer.label'),
        ];
    }

    public function messages(): array
    {
        $messages = [];

        foreach (['required', 'string', 'min', 'max'] as $rule) {
            $key = "messages.validation.store_competitions.correct_answer.{$rule}";
            if (__($key) !== $key) {
                $messages["correct_answer.{$rule}"] = __($key);
            }
        }

        return $messages;
    }
}

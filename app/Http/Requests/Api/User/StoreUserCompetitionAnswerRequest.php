<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\ApiRequest;

class StoreUserCompetitionAnswerRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (is_string($this->input('answer'))) {
            $this->merge(['answer' => trim($this->input('answer'))]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'answer' => ['required', 'string', 'min:3', 'max:180', 'regex:/[^\s\x{064B}-\x{065F}\x{0670}]/u'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'answer' => __('messages.validation.user_competitions.answer.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'answer.required' => __('messages.validation.user_competitions.answer.required'),
            'answer.string' => __('messages.validation.user_competitions.answer.string'),
            'answer.min' => __('messages.validation.user_competitions.answer.min'),
            'answer.max' => __('messages.validation.user_competitions.answer.max'),
            'answer.regex' => __('messages.validation.user_competitions.answer.regex'),
        ];
    }
}

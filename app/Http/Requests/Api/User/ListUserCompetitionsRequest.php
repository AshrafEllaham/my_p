<?php

namespace App\Http\Requests\Api\User;

use App\Enums\UserCompetitionStatusFilterEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListUserCompetitionsRequest extends LookupListRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if (! $this->has('pagination')) {
            $this->merge(['pagination' => 'on']);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            ...$this->paginationRules(),
            'page' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', Rule::enum(UserCompetitionStatusFilterEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'page' => __('messages.validation.catalog_lookup.page.label'),
            'status' => __('messages.validation.user_competitions.status.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
            'status.string' => __('messages.validation.user_competitions.status.string'),
            'status.enum' => __('messages.validation.user_competitions.status.enum'),
        ];
    }
}

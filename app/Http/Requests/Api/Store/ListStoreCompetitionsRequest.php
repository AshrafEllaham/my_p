<?php

namespace App\Http\Requests\Api\Store;

use App\Enums\CompetitionStatusEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListStoreCompetitionsRequest extends LookupListRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if (! $this->has('pagination')) {
            $this->merge(['pagination' => 'on']);
        }
    }

    public function rules(): array
    {
        return $this->paginationRules() + [
            'page' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', Rule::enum(CompetitionStatusEnum::class)],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'page' => __('messages.validation.store_competitions.page.label'),
            'status' => __('messages.validation.store_competitions.status.label'),
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'page.integer' => __('messages.validation.store_competitions.page.integer'),
            'page.min' => __('messages.validation.store_competitions.page.min'),
            'status.string' => __('messages.validation.store_competitions.status.string'),
            'status.enum' => __('messages.validation.store_competitions.status.enum'),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\User;

use App\Enums\FavoriteTypeEnum;
use App\Http\Requests\Api\Catalog\LookupListRequest;
use Illuminate\Validation\Rule;

class ListUserFavoritesRequest extends LookupListRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            ...$this->paginationRules(),
            'page' => ['sometimes', 'integer', 'min:1'],
            'type' => ['sometimes', 'string', Rule::enum(FavoriteTypeEnum::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'page' => __('messages.validation.catalog_lookup.page.label'),
            'type' => __('messages.validation.favorite_list.type.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'page.integer' => __('messages.validation.catalog_lookup.page.integer'),
            'page.min' => __('messages.validation.catalog_lookup.page.min'),
            'type.string' => __('messages.validation.favorite_list.type.string'),
            'type.enum' => __('messages.validation.favorite_list.type.enum'),
        ];
    }
}

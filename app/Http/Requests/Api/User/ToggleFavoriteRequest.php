<?php

namespace App\Http\Requests\Api\User;

use App\Enums\FavoriteTypeEnum;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ToggleFavoriteRequest extends ApiRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $inputType = $this->input('type');
        $type = is_string($inputType) ? FavoriteTypeEnum::tryFrom($inputType) : null;
        $table = $type === FavoriteTypeEnum::Store ? 'stores' : 'products';
        $favoriteableExists = Rule::exists($table, 'id');

        if ($type === FavoriteTypeEnum::Product) {
            $favoriteableExists->whereNull('deleted_at');
        }

        return [
            'type' => ['required', 'string', Rule::enum(FavoriteTypeEnum::class)],
            'favoriteable_id' => ['required', 'integer', 'min:1', $favoriteableExists],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'type' => __('messages.validation.favorite_toggle.type.label'),
            'favoriteable_id' => __('messages.validation.favorite_toggle.favoriteable_id.label'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'type.required' => __('messages.validation.favorite_toggle.type.required'),
            'type.string' => __('messages.validation.favorite_toggle.type.string'),
            'type.enum' => __('messages.validation.favorite_toggle.type.enum'),
            'favoriteable_id.required' => __('messages.validation.favorite_toggle.favoriteable_id.required'),
            'favoriteable_id.integer' => __('messages.validation.favorite_toggle.favoriteable_id.integer'),
            'favoriteable_id.min' => __('messages.validation.favorite_toggle.favoriteable_id.min'),
            'favoriteable_id.exists' => __('messages.validation.favorite_toggle.favoriteable_id.exists'),
        ];
    }
}

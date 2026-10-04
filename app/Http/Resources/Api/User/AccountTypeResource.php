<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class AccountTypeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_type' => $this->account_type?->value,
            'country_id' => $this->country_id,
            'governorate_id' => $this->governorate_id,
            'city_id' => $this->city_id,
        ];
    }
}

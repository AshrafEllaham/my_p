<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class RegisteredUserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_code' => $this->phone_code,
            'phone' => $this->phone,
            'account_type' => $this->account_type?->value,
            'onboarding' => $this->resource->getAttribute('onboarding'),
            'status' => $this->status?->value,
            'access_token' => $this->access_token,
        ];
    }
}

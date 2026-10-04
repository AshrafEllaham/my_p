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
            'email' => $this->email,
            'phone_code' => $this->phone_code,
            'phone' => $this->phone,
            'status' => $this->status?->value,
            'verification_required' => false,
            'access_token' => $this->access_token,
            'token_type' => 'bearer',
        ];
    }
}

<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->avatar,
            'address' => $this->address_line,
        ];
    }
}

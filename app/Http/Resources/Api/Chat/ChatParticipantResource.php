<?php

namespace App\Http\Resources\Api\Chat;

use App\Models\Sai\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class ChatParticipantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar ? get_file($this->avatar) : null,
            'account_type' => $this->account_type?->value,
        ];
    }
}

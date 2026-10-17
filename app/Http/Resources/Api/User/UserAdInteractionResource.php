<?php

namespace App\Http\Resources\Api\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAdInteractionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'event_id' => $this->resource['event_id'],
            'type' => $this->resource['type']->value,
            'recorded' => $this->resource['recorded'],
        ];
    }
}

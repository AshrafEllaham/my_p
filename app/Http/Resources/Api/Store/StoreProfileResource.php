<?php

namespace App\Http\Resources\Api\Store;

use App\Models\Sai\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Store */
class StoreProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->whenLoaded('owner', fn () => $this->owner->name),
            'image' => $this->whenLoaded('owner', fn () => $this->owner->avatar),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'cover' => $this->cover_image,
            'address' => $this->whenLoaded('owner', fn () => $this->owner->address_line),
        ];
    }
}

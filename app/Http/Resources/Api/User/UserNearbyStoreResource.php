<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Store */
class UserNearbyStoreResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $categoryTranslation = $this->relationLoaded('category') && $this->category?->relationLoaded('translations')
            ? $this->category->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
            'image' => $this->whenLoaded('owner', fn () => $this->owner?->avatar ? get_file($this->owner->avatar) : null),
            'cover' => $this->cover_image ? get_file($this->cover_image) : null,
            'category_id' => $this->category_id,
            'category_name' => $categoryTranslation?->name,
            'address' => $this->whenLoaded('owner', fn () => $this->owner?->address_line),
            'is_favorite' => (bool) ($this->is_favorite ?? false),
            'average_rating' => $this->average_rating === null ? null : round((float) $this->average_rating, 2),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'distance_km' => $this->distance_km === null ? null : round((float) $this->distance_km, 2),
        ];
    }
}

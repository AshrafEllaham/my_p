<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class UserProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $media = $this->relationLoaded('media') ? $this->media->first() : null;
        $categoryTranslation = $this->relationLoaded('category') && $this->category?->relationLoaded('translations')
            ? $this->category->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $categoryTranslation?->name,
            'name' => $this->name,
            'price' => $this->price,
            'original_price' => $this->original_price,
            'discount_percentage' => $this->discount_percentage,
            'image' => $media?->path ? get_file($media->path) : null,
            'is_favorite' => (bool) ($this->is_favorite ?? false),
            'average_rating' => $this->average_rating === null ? null : round((float) $this->average_rating, 2),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'units_sold' => (int) ($this->units_sold ?? 0),
        ];
    }
}

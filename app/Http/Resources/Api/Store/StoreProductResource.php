<?php

namespace App\Http\Resources\Api\Store;

use App\Models\Sai\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class StoreProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                $translations = $this->category->relationLoaded('translations')
                    ? $this->category->getRelation('translations')
                    : collect();

                return [
                    'id' => $this->category->id,
                    'name' => $translations->firstWhere('locale', app()->getLocale())?->name,
                ];
            }),
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'status' => $this->status->value,
            'price' => $this->price,
            'original_price' => $this->original_price,
            'discount_percentage' => $this->discount_percentage,
            'discount_ends_at' => $this->discount_ends_at?->toISOString(),
            'stock_quantity' => $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'published_at' => $this->published_at?->toISOString(),
            'media' => $this->whenLoaded('media', fn () => $this->media->map(fn ($media) => [
                'id' => $media->id,
                'type' => $media->type->value,
                'path' => $media->path,
                'is_primary' => $media->is_primary,
            ])),
            'features' => $this->whenLoaded('features', fn () => $this->features->pluck('name')),
        ];
    }
}

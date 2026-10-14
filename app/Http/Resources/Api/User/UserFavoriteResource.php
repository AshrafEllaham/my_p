<?php

namespace App\Http\Resources\Api\User;

use App\Enums\FavoriteTypeEnum;
use App\Models\Sai\Favorite;
use App\Models\Sai\Product;
use App\Models\Sai\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Favorite */
class UserFavoriteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $type = $this->type;

        return [
            'id' => $this->id,
            'type' => $type->value,
            'favoriteable_id' => $this->favoriteable_id,
            'created_at' => $this->created_at,
            'item' => $type === FavoriteTypeEnum::Product
                ? $this->whenLoaded('product', fn (?Product $product) => $this->productItem($product))
                : $this->whenLoaded('store', fn (?Store $store) => $this->storeItem($store)),
        ];
    }

    /** @return array<string, mixed>|null */
    private function productItem(?Product $product): ?array
    {
        if ($product === null) {
            return null;
        }

        $media = $product->relationLoaded('media') ? $product->getRelation('media')->first() : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category_id' => $product->category_id,
            'category_name' => $this->categoryName($product),
            'store_name' => $product->relationLoaded('store') ? $product->getRelation('store')?->name : null,
            'price' => $product->price,
            'original_price' => $product->original_price,
            'discount_percentage' => $product->discount_percentage,
            'image' => $media?->path ? get_file($media->path) : null,
            'average_rating' => $product->average_rating === null ? null : round((float) $product->average_rating, 2),
            'reviews_count' => (int) ($product->reviews_count ?? 0),
        ];
    }

    /** @return array<string, mixed>|null */
    private function storeItem(?Store $store): ?array
    {
        if ($store === null) {
            return null;
        }

        $owner = $store->relationLoaded('owner') ? $store->getRelation('owner') : null;

        return [
            'id' => $store->id,
            'owner_id' => $store->owner_id,
            'name' => $owner?->name,
            'image' => $owner?->avatar ? get_file($owner->avatar) : null,
            'cover' => $store->cover_image ? get_file($store->cover_image) : null,
            'category_id' => $store->category_id,
            'category_name' => $this->categoryName($store),
            'average_rating' => $store->average_rating === null ? null : round((float) $store->average_rating, 2),
            'reviews_count' => (int) ($store->reviews_count ?? 0),
        ];
    }

    private function categoryName(Product|Store $item): ?string
    {
        if (! $item->relationLoaded('category')) {
            return null;
        }

        $category = $item->getRelation('category');
        if ($category === null || ! $category->relationLoaded('translations')) {
            return null;
        }

        return $category->getRelation('translations')->firstWhere('locale', app()->getLocale())?->name;
    }
}

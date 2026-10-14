<?php

namespace App\Http\Resources\Api\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProductDetailsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $product = $this['product'];
        $policy = $this['policy'];
        $review = $this['latest_review'];
        $categoryTranslation = $product->relationLoaded('category') && $product->category?->relationLoaded('translations')
            ? $product->category->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            'id' => $product->id,
            'store_id' => $product->store_id,
            'category_id' => $product->category_id,
            'category_name' => $categoryTranslation?->name,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'original_price' => $product->original_price,
            'discount_percentage' => $product->discount_percentage,
            'discount_ends_at' => $product->discount_ends_at?->toIso8601String(),
            'stock_quantity' => (int) $product->stock_quantity,
            'is_favorite' => (bool) $product->is_favorite,
            'average_rating' => $product->average_rating === null ? null : round((float) $product->average_rating, 2),
            'reviews_count' => (int) $product->reviews_count,
            'media' => $product->relationLoaded('media') ? $product->media->map(fn ($media) => [
                'id' => $media->id,
                'type' => $media->type->value,
                'url' => $media->path ? get_file($media->path) : null,
                'is_primary' => (bool) $media->is_primary,
            ])->all() : [],
            'features' => $product->relationLoaded('features') ? $product->features->pluck('name')->all() : [],
            'store' => $product->relationLoaded('store') ? [
                'id' => $product->store?->id,
                'name' => $product->store?->name,
                'avatar' => $product->store?->avatar ? get_file($product->store->avatar) : null,
                'phone_code' => $product->store?->phone_code,
                'phone' => $product->store?->phone,
                'governorate_id' => $product->store?->governorate_id,
            ] : null,
            'policy' => $policy ? [
                'pickup_hold_hours' => $policy->pickup_hold_hours,
                'return_window_days' => $policy->return_window_days,
                'returns_enabled' => $policy->returns_enabled,
                'exchanges_enabled' => $policy->exchanges_enabled,
                'cancellation_enabled' => $policy->cancellation_enabled,
                'pickup_instructions' => $policy->pickup_instructions,
                'return_terms' => $policy->return_terms,
                'exchange_terms' => $policy->exchange_terms,
                'cancellation_terms' => $policy->cancellation_terms,
            ] : null,
            'latest_review' => $review ? [
                'user_name' => $review->relationLoaded('user') ? $review->user?->name : null,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toIso8601String(),
            ] : null,
            'similar_products' => UserProductResource::collection($this['similar_products'])->resolve($request),
        ];
    }
}

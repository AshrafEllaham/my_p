<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Store */
class UserStoreDetailsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $store = $this['store'];
        $owner = $store->relationLoaded('owner') ? $store->owner : null;
        $categoryTranslation = $store->relationLoaded('category') && $store->category?->relationLoaded('translations')
            ? $store->category->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;
        $governorate = $owner?->relationLoaded('governorate') ? $owner->governorate : null;
        $governorateTranslation = $governorate?->relationLoaded('translations')
            ? $governorate->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;
        $city = $owner?->relationLoaded('city') ? $owner->city : null;
        $cityTranslation = $city?->relationLoaded('translations')
            ? $city->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;
        $policy = $this['policy'];

        return [
            'id' => $store->id,
            'owner_id' => $store->owner_id,
            'name' => $owner?->name,
            'image' => $owner?->avatar ? get_file($owner->avatar) : null,
            'cover' => $store->cover_image ? get_file($store->cover_image) : null,
            'description' => $store->description,
            'category_id' => $store->category_id,
            'category_name' => $categoryTranslation?->name,
            'phone_code' => $owner?->phone_code,
            'phone' => $owner?->phone,
            'governorate_id' => $owner?->governorate_id,
            'governorate_name' => $governorateTranslation?->name,
            'city_id' => $owner?->city_id,
            'city_name' => $cityTranslation?->name,
            'address' => $owner?->address_line,
            'latitude' => $store->latitude === null ? null : (float) $store->latitude,
            'longitude' => $store->longitude === null ? null : (float) $store->longitude,
            'distance_km' => $store->distance_km === null ? null : round((float) $store->distance_km, 2),
            'average_rating' => $store->average_rating === null ? null : round((float) $store->average_rating, 2),
            'reviews_count' => (int) $store->reviews_count,
            'products_count' => (int) $store->products_count,
            'is_favorite' => (bool) $store->is_favorite,
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
            'products' => UserProductResource::collection($this['products'])->resolve($request),
        ];
    }
}

<?php

namespace App\Http\Resources\Api\Catalog;

use App\Models\Sai\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin City */
class CityLookupResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $translation = $this->resource->relationLoaded('translations')
            ? $this->resource->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            'id' => $this->id,
            'governorate_id' => $this->governorate_id,
            'name' => $translation?->name,
        ];
    }
}

<?php

namespace App\Http\Resources\Api\Catalog;

use App\Models\Sai\Governorate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Governorate */
class GovernorateLookupResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $translation = $this->resource->relationLoaded('translations')
            ? $this->resource->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            'id' => $this->id,
            'country_id' => $this->country_id,
            'name' => $translation?->name,
        ];
    }
}

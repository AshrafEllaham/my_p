<?php

namespace App\Http\Resources\Api\Catalog;

use App\Models\Sai\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Country */
class CountryLookupResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $translation = $this->resource->relationLoaded('translations')
            ? $this->resource->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            'id' => $this->id,
            'name' => $translation?->name,
            'code' => $this->code,
            'phone_code' => $this->phone_code,
            'flag' => $this->flag,
        ];
    }
}

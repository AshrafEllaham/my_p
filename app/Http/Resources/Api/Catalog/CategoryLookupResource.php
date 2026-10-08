<?php

namespace App\Http\Resources\Api\Catalog;

use App\Models\Sai\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Category */
class CategoryLookupResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $translation = $this->resource->relationLoaded('translations')
            ? $this->resource->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $translation?->name,
            'description' => $translation?->description,
            'image' => $this->image ? get_file($this->image) : null,
        ];
    }
}

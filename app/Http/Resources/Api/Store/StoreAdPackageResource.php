<?php

namespace App\Http\Resources\Api\Store;

use App\Models\Sai\AdPackage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AdPackage */
class StoreAdPackageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $translations = $this->relationLoaded('translations') ? $this->getRelation('translations') : collect();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $translations->firstWhere('locale', app()->getLocale())?->name,
            'description' => $translations->firstWhere('locale', app()->getLocale())?->description,
            'duration_days' => $this->duration_days,
            'price' => $this->price,
            'currency' => $this->currency,
        ];
    }
}

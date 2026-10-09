<?php

namespace App\Http\Resources\Api\Store;

use App\Models\Sai\Ad;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ad */
class StoreAdResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'store_id' => $this->store_id,
            'ad_package_id' => $this->ad_package_id,
            'product_id' => $this->product_id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'action_label' => $this->action_label,
            'caption' => $this->caption,
            'placement' => $this->placement->value,
            'action' => $this->action->value,
            'media_type' => $this->media_type->value,
            'media_path' => $this->media_path,
            'status' => $this->status->value,
            'cost' => $this->cost,
            'currency' => $this->currency,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

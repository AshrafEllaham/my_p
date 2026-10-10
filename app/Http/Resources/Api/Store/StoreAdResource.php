<?php

namespace App\Http\Resources\Api\Store;

use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\Ad;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ad */
class StoreAdResource extends JsonResource
{
    use FormatsTimestamps;

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
            'media_path' => get_file($this->media_path),
            'status' => $this->status->value,
            'cost' => $this->cost,
            'currency' => $this->currency,
            'starts_at' => $this->formatTimestamp($this->starts_at),
            'ends_at' => $this->formatTimestamp($this->ends_at),
            'created_at' => $this->formatTimestamp($this->created_at),
        ];
    }
}

<?php

namespace App\Http\Resources\Api\User;

use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\Ad;
use App\Models\Sai\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ad */
class UserStoreAdResource extends JsonResource
{
    use FormatsTimestamps;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'product_id' => $this->product_id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'action_label' => $this->action_label,
            'caption' => $this->caption,
            'placement' => $this->placement->value,
            'action' => $this->action->value,
            'media_type' => $this->media_type->value,
            'media_path' => get_file($this->media_path),
            'media_url' => get_file($this->media_path),
            'target' => [
                'store_id' => $this->store_id,
                'product_id' => $this->product_id,
                'category_id' => $this->category_id,
            ],
            'store' => $this->whenLoaded('store', static function (Store $store): array {
                $owner = $store->relationLoaded('owner') ? $store->owner : null;

                return [
                    'id' => (int) $store->getKey(),
                    'owner_id' => (int) $store->owner_id,
                    'name' => $owner?->name,
                    'avatar' => $owner?->avatar ? get_file($owner->avatar) : null,
                    'cover' => $store->cover_image ? get_file($store->cover_image) : null,
                ];
            }),
            'starts_at' => $this->formatTimestamp($this->starts_at),
            'ends_at' => $this->formatTimestamp($this->ends_at),
            'starts_at_iso' => $this->starts_at?->toIso8601String(),
            'ends_at_iso' => $this->ends_at?->toIso8601String(),
        ];
    }
}

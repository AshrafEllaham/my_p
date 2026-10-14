<?php

namespace App\Http\Resources\Api\User;

use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\Ad;
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
            'starts_at' => $this->formatTimestamp($this->starts_at),
            'ends_at' => $this->formatTimestamp($this->ends_at),
        ];
    }
}

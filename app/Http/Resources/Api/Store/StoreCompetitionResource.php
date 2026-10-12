<?php

namespace App\Http\Resources\Api\Store;

use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\Competition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Competition */
class StoreCompetitionResource extends JsonResource
{
    use FormatsTimestamps;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'title' => $this->title,
            'question' => $this->question,
            'prize' => $this->prize,
            'image' => $this->image === null
                ? null
                : (filter_var($this->image, FILTER_VALIDATE_URL) ? $this->image : Storage::disk('public')->url($this->image)),
            'status' => $this->status->value,
            'ends_at' => $this->formatTimestamp($this->ends_at),
            'drawn_at' => $this->formatTimestamp($this->drawn_at),
            'entries_count' => $this->whenCounted('entries'),
            'created_at' => $this->formatTimestamp($this->created_at),
        ];
    }
}

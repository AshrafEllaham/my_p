<?php

namespace App\Http\Resources\Api\User;

use App\Enums\UserCompetitionStatusFilterEnum;
use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\Competition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Competition */
class UserCompetitionResource extends JsonResource
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
            'image' => $this->storageUrl($this->image),
            'status' => UserCompetitionStatusFilterEnum::from($this->feed_status)->value,
            'ends_at' => $this->formatTimestamp($this->ends_at),
            'store' => $this->whenLoaded('store', function (): array {
                $store = $this->store;

                return [
                    'id' => $store->id,
                    'name' => $store->owner?->name,
                    'image' => $this->storageUrl($store->cover_image ?? $store->owner?->avatar),
                ];
            }),
        ];
    }

    private function storageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return filter_var($path, FILTER_VALIDATE_URL)
            ? $path
            : Storage::disk('public')->url($path);
    }
}

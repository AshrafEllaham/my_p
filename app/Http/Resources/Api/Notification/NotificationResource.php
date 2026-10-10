<?php

namespace App\Http\Resources\Api\Notification;

use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Notification */
class NotificationResource extends JsonResource
{
    use FormatsTimestamps;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->data['title'] ?? null,
            'message' => $this->data['message'] ?? $this->data['text'] ?? null,
            'action_url' => $this->data['action_url'] ?? $this->data['href'] ?? null,
            'data' => $this->data,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->formatTimestamp($this->read_at),
            'created_at' => $this->formatTimestamp($this->created_at),
        ];
    }
}

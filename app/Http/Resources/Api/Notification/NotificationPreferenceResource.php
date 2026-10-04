<?php

namespace App\Http\Resources\Api\Notification;

use App\Models\Sai\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NotificationPreference */
class NotificationPreferenceResource extends JsonResource
{
    /** @return array<string, bool> */
    public function toArray(Request $request): array
    {
        return [
            'orders_enabled' => $this->orders_enabled,
            'pickup_enabled' => $this->pickup_enabled,
            'returns_enabled' => $this->returns_enabled,
            'chats_enabled' => $this->chats_enabled,
            'offers_enabled' => $this->offers_enabled,
        ];
    }
}

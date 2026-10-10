<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContactUs */
class ContactUsResource extends JsonResource
{
    use FormatsTimestamps;

    /** @return array<string, int|string> */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'created_at' => $this->formatTimestamp($this->created_at) ?? '',
        ];
    }
}

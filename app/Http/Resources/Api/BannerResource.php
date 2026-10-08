<?php

namespace App\Http\Resources\Api;

use App\Models\Sai\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Banner */
class BannerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => get_file($this->file),
            'type' => $this->type->value,
        ];
    }
}

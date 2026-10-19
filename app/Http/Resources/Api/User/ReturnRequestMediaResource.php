<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\ReturnRequestMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReturnRequestMedia */
class ReturnRequestMediaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => asset('storage/'.$this->path),
            'mime_type' => $this->mime_type,
            'sort_order' => $this->sort_order,
        ];
    }
}

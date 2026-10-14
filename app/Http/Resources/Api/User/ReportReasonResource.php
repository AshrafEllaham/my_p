<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\ReportReason;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReportReason */
class ReportReasonResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'title' => $this->whenLoaded('translations', fn () => $this->translations->first()?->title),
        ];
    }
}

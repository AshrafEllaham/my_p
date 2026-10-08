<?php

namespace App\Http\Resources\Api;

use App\Models\Sai\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Faq */
class FaqResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $translation = $this->resource->relationLoaded('translations')
            ? $this->resource->getRelation('translations')->firstWhere('locale', app()->getLocale())
            : null;

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'question' => $translation?->question,
            'answer' => $translation?->answer,
        ];
    }
}

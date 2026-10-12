<?php

namespace App\Http\Resources\Api\Store;

use App\Services\Sai\Results\CompetitionDrawResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CompetitionDrawResult */
class StoreCompetitionDrawResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'competition' => StoreCompetitionDetailsResource::make($this->competition)->resolve($request),
            'winner' => [
                'id' => $this->winner->user_id,
                'name' => $this->winner->user?->name,
                'answer' => $this->winner->answer,
            ],
            'eligible_count' => $this->eligibleCount,
        ];
    }
}

<?php

namespace App\Http\Resources\Api\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreCompetitionDetailsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...StoreCompetitionResource::make($this->resource)->resolve($request),
            'winner_user_id' => $this->winner_user_id,
            'correct_answer' => $this->correct_answer,
            'winner' => $this->whenLoaded('winnerUser', fn () => $this->winnerUser === null ? null : [
                'id' => $this->winnerUser->id,
                'name' => $this->winnerUser->name,
                'answer' => $this->whenLoaded('winnerEntry', fn () => $this->winnerEntry?->answer),
            ]),
        ];
    }
}

<?php

namespace App\Http\Resources\Api\User;

use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\CompetitionEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CompetitionEntry */
class UserCompetitionEntryResource extends JsonResource
{
    use FormatsTimestamps;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'answer' => $this->answer,
            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),
        ];
    }
}

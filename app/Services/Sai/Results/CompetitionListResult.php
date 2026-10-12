<?php

namespace App\Services\Sai\Results;

use Illuminate\Database\Eloquent\Builder;

final readonly class CompetitionListResult
{
    /** @param array{active_count: int, total_participation_count: int, drawn_count: int} $summary */
    public function __construct(
        public Builder $query,
        public array $summary,
    ) {}
}

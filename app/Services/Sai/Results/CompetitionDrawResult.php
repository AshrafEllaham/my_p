<?php

namespace App\Services\Sai\Results;

use App\Models\Sai\Competition;
use App\Models\Sai\CompetitionEntry;

final readonly class CompetitionDrawResult
{
    public function __construct(
        public Competition $competition,
        public CompetitionEntry $winner,
        public int $eligibleCount,
    ) {}
}

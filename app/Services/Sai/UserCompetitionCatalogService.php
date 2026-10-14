<?php

namespace App\Services\Sai;

use App\Enums\UserCompetitionStatusFilterEnum;
use App\Models\Sai\Competition;
use App\Repositories\Sai\CompetitionRepository;
use Illuminate\Database\Eloquent\Builder;

class UserCompetitionCatalogService
{
    public function __construct(private readonly CompetitionRepository $competitions) {}

    /** @param array<string, mixed> $filters
     * @return Builder<Competition>
     */
    public function list(array $filters): Builder
    {
        $status = isset($filters['status'])
            ? UserCompetitionStatusFilterEnum::from($filters['status'])
            : null;

        return $this->competitions->listForUser($status);
    }
}

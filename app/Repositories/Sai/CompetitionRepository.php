<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Enums\CompetitionStatusEnum;
use App\Enums\UserCompetitionStatusFilterEnum;
use App\Enums\UserStatusEnum;
use App\Models\Sai\Competition;
use App\Models\Sai\CompetitionEntry;
use App\Models\Sai\Store;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompetitionRepository extends MainRepository
{
    public function __construct(Competition $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function isStoreOwner(int $ownerId): bool
    {
        return Store::query()->where('owner_id', $ownerId)
            ->whereHas('owner', fn (Builder $query) => $query->where('account_type', AccountTypeEnum::Store))
            ->exists();
    }

    public function storeIdForOwner(int $ownerId): int
    {
        return (int) Store::query()->where('owner_id', $ownerId)->value('id');
    }

    /** @param array<string, mixed> $filters
     * @return Builder<Competition>
     */
    public function listForStore(int $storeId, array $filters = []): Builder
    {
        return $this->query()->select([
            'id', 'public_id', 'store_id', 'title', 'question', 'prize', 'image', 'status', 'ends_at', 'drawn_at', 'created_at',
        ])->withCount('entries')
            ->where('store_id', $storeId)
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('status', CompetitionStatusEnum::from($filters['status'])))
            ->orderByDesc('created_at')->orderByDesc('id');
    }

    /** @return Builder<Competition> */
    public function listForUser(?UserCompetitionStatusFilterEnum $status = null): Builder
    {
        $now = now();

        $query = $this->query()
            ->select([
                'id', 'public_id', 'store_id', 'title', 'question', 'prize', 'image', 'status', 'ends_at', 'created_at',
            ])
            ->selectRaw(
                'CASE WHEN competitions.status = ? AND competitions.ends_at > ? THEN ? ELSE ? END AS feed_status',
                [
                    CompetitionStatusEnum::Active->value,
                    $now,
                    UserCompetitionStatusFilterEnum::AvailableNow->value,
                    UserCompetitionStatusFilterEnum::Ended->value,
                ],
            )
            ->with([
                'store:id,owner_id,cover_image',
                'store.owner:id,name,avatar',
            ])
            ->whereHas('store', fn (Builder $store) => $store
                ->whereHas('owner', fn (Builder $owner) => $owner
                    ->where('account_type', AccountTypeEnum::Store)
                    ->where('status', UserStatusEnum::Active))
                ->whereHas('category', fn (Builder $category) => $category->where('is_active', true)))
            ->whereIn('status', [
                CompetitionStatusEnum::Active,
                CompetitionStatusEnum::Ended,
                CompetitionStatusEnum::Drawn,
            ]);

        if ($status === UserCompetitionStatusFilterEnum::AvailableNow) {
            $query->where('status', CompetitionStatusEnum::Active)
                ->where('ends_at', '>', $now);
        } elseif ($status === UserCompetitionStatusFilterEnum::Ended) {
            $query->where(function (Builder $ended) use ($now): void {
                $ended->whereIn('status', [CompetitionStatusEnum::Ended, CompetitionStatusEnum::Drawn])
                    ->orWhere(fn (Builder $active) => $active
                        ->where('status', CompetitionStatusEnum::Active)
                        ->where('ends_at', '<=', $now));
            });
        }

        if ($status === UserCompetitionStatusFilterEnum::AvailableNow) {
            return $query->orderBy('ends_at')->orderByDesc('id');
        }

        if ($status === UserCompetitionStatusFilterEnum::Ended) {
            return $query->orderByDesc('ends_at')->orderByDesc('id');
        }

        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /** @return array{active_count: int, total_participation_count: int, drawn_count: int} */
    public function summaryForStore(int $storeId): array
    {
        $statuses = $this->query()->where('store_id', $storeId)
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS active_count', [CompetitionStatusEnum::Active->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS drawn_count', [CompetitionStatusEnum::Drawn->value])
            ->first();

        return [
            'active_count' => (int) ($statuses->active_count ?? 0),
            'total_participation_count' => (int) CompetitionEntry::query()
                ->whereHas('competition', fn (Builder $query) => $query->where('store_id', $storeId))
                ->count(),
            'drawn_count' => (int) ($statuses->drawn_count ?? 0),
        ];
    }

    public function findForStore(int|string $id, int $storeId): Competition
    {
        return $this->query()->where('store_id', $storeId)->withCount('entries')->findOrFail($id);
    }

    public function findDetailsForStore(int|string $id, int $storeId): Competition
    {
        $competition = $this->query()->where('store_id', $storeId)
            ->withCount('entries')
            ->with('winnerUser:id,name')
            ->findOrFail($id);

        $this->loadWinnerEntry($competition);

        return $competition;
    }

    public function findForStoreForUpdate(int|string $id, int $storeId): Competition
    {
        return $this->query()->where('store_id', $storeId)->lockForUpdate()->findOrFail($id);
    }

    public function countMatchingEntries(int $competitionId, string $normalizedAnswer): int
    {
        return CompetitionEntry::query()->where('competition_id', $competitionId)
            ->where('normalized_answer', $normalizedAnswer)
            ->count();
    }

    public function randomMatchingEntry(int $competitionId, string $normalizedAnswer): ?CompetitionEntry
    {
        return CompetitionEntry::query()->select(['id', 'competition_id', 'user_id', 'answer'])
            ->with('user:id,name')
            ->where('competition_id', $competitionId)
            ->where('normalized_answer', $normalizedAnswer)
            ->inRandomOrder()
            ->first();
    }

    public function completeDraw(
        Competition $competition,
        string $correctAnswer,
        string $normalizedAnswer,
        CompetitionEntry $winner,
    ): Competition {
        $competition->fill([
            'correct_answer' => $correctAnswer,
            'normalized_correct_answer' => $normalizedAnswer,
            'winner_user_id' => $winner->user_id,
            'status' => CompetitionStatusEnum::Drawn,
            'drawn_at' => now(),
        ]);
        $competition->save();

        $competition->refresh()->loadCount('entries')->load('winnerUser:id,name');
        $competition->setRelation('winnerEntry', $winner);

        return $competition;
    }

    private function loadWinnerEntry(Competition $competition): void
    {
        if ($competition->winner_user_id === null) {
            $competition->setRelation('winnerEntry', null);

            return;
        }

        $winnerEntry = CompetitionEntry::query()->select(['id', 'competition_id', 'user_id', 'answer'])
            ->where('competition_id', $competition->id)
            ->where('user_id', $competition->winner_user_id)
            ->first();

        $competition->setRelation('winnerEntry', $winnerEntry);
    }

    /** @param array<string, mixed> $data */
    public function createForStore(array $data): Competition
    {
        return $this->getModel()->newQuery()->create($data)->refresh()->loadCount('entries');
    }

    /** @param array<string, mixed> $data */
    public function updateForStore(int|string $id, int $storeId, array $data): Competition
    {
        $competition = $this->findForStore($id, $storeId);
        $competition->fill($data);
        $competition->save();

        return $competition->refresh()->loadCount('entries');
    }

    public function deleteForStore(int|string $id, int $storeId): bool
    {
        return (bool) $this->findForStore($id, $storeId)->delete();
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createRecord(array $data): Model
    {
        $record = $this->getModel()->newInstance();
        $record->fill($data);
        $record->save();

        return $record;
    }

    /** @param array<string, mixed> $data */
    public function updateRecord(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $record;
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}

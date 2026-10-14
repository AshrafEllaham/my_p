<?php

namespace App\Services\Sai;

use App\Enums\FavoriteTypeEnum;
use App\Repositories\Sai\FavoriteRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FavoriteService
{
    public function __construct(private readonly FavoriteRepository $repository) {}

    public function toggle(int $userId, FavoriteTypeEnum $type, int $favoriteableId): bool
    {
        return DB::transaction(function () use ($userId, $type, $favoriteableId): bool {
            if ($this->repository->existsForUser($userId, $type, $favoriteableId)) {
                $this->repository->deleteForUser($userId, $type, $favoriteableId);

                return false;
            }

            $this->repository->createForUser($userId, $type, $favoriteableId);

            return true;
        });
    }

    /** @param array<string, mixed> $filters */
    public function list(int $userId, array $filters, string $locale): Builder
    {
        $type = isset($filters['type'])
            ? FavoriteTypeEnum::from((string) $filters['type'])
            : null;

        return $this->repository->listForUser($userId, $type, $locale);
    }
}

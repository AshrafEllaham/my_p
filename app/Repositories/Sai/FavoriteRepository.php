<?php

namespace App\Repositories\Sai;

use App\Enums\FavoriteTypeEnum;
use App\Models\Sai\Favorite;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class FavoriteRepository extends MainRepository
{
    public function __construct(Favorite $model)
    {
        $this->model = $model;
    }

    public function existsForUser(int $userId, FavoriteTypeEnum $type, int $favoriteableId): bool
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('favoriteable_id', $favoriteableId)
            ->exists();
    }

    public function deleteForUser(int $userId, FavoriteTypeEnum $type, int $favoriteableId): void
    {
        $this->query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('favoriteable_id', $favoriteableId)
            ->delete();
    }

    public function createForUser(int $userId, FavoriteTypeEnum $type, int $favoriteableId): void
    {
        $this->getModel()->newQuery()->create([
            'user_id' => $userId,
            'type' => $type,
            'favoriteable_id' => $favoriteableId,
        ]);
    }

    private function query(): Builder
    {
        return $this->getModel()->newQuery();
    }
}

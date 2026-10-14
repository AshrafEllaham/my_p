<?php

namespace App\Repositories\Sai;

use App\Enums\FavoriteTypeEnum;
use App\Models\Sai\Favorite;
use App\Models\Sai\Review;
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

    public function listForUser(int $userId, ?FavoriteTypeEnum $type, string $locale): Builder
    {
        $relations = [];

        if ($type !== FavoriteTypeEnum::Store) {
            $relations['product'] = fn ($query) => $query
                ->select(['id', 'store_id', 'category_id', 'name', 'price', 'original_price', 'discount_percentage'])
                ->with([
                    'media' => fn ($mediaQuery) => $mediaQuery
                        ->select(['id', 'product_id', 'path', 'is_primary'])
                        ->orderByDesc('is_primary')
                        ->orderBy('id'),
                    'category' => fn ($categoryQuery) => $categoryQuery
                        ->select(['id', 'parent_id'])
                        ->with(['translations' => fn ($translationQuery) => $translationQuery
                            ->select(['id', 'category_id', 'locale', 'name'])
                            ->where('locale', $locale)]),
                    'store:id,name',
                ])
                ->addSelect([
                    'average_rating' => Review::query()
                        ->selectRaw('AVG(reviews.rating)')
                        ->whereColumn('reviews.product_id', 'products.id')
                        ->where('reviews.is_visible', true),
                    'reviews_count' => Review::query()
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('reviews.product_id', 'products.id')
                        ->where('reviews.is_visible', true),
                ]);
        }

        if ($type !== FavoriteTypeEnum::Product) {
            $relations['store'] = fn ($query) => $query
                ->select(['id', 'owner_id', 'category_id', 'cover_image'])
                ->with([
                    'owner:id,name,avatar',
                    'category' => fn ($categoryQuery) => $categoryQuery
                        ->select(['id'])
                        ->with(['translations' => fn ($translationQuery) => $translationQuery
                            ->select(['id', 'category_id', 'locale', 'name'])
                            ->where('locale', $locale)]),
                ])
                ->addSelect([
                    'average_rating' => Review::query()
                        ->selectRaw('AVG(reviews.rating)')
                        ->whereColumn('reviews.store_id', 'stores.owner_id')
                        ->where('reviews.is_visible', true),
                    'reviews_count' => Review::query()
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('reviews.store_id', 'stores.owner_id')
                        ->where('reviews.is_visible', true),
                ]);
        }

        return $this->getModel()->newQuery()
            ->select(['id', 'user_id', 'type', 'favoriteable_id', 'created_at'])
            ->where('user_id', $userId)
            ->when($type !== null, fn (Builder $query) => $query->where('type', $type))
            ->with($relations)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function query(): Builder
    {
        return $this->getModel()->newQuery();
    }
}

<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\UserStatusEnum;
use App\Enums\UserStoreSortEnum;
use App\Helpers\ImageHelper;
use App\Models\Sai\Product;
use App\Models\Sai\Review;
use App\Models\Sai\Store;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StoreRepository extends MainRepository
{
    public function __construct(Store $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function ensureVisibleForUser(int $id): void
    {
        $this->query()
            ->select(['id'])
            ->whereKey($id)
            ->whereHas('owner', fn (Builder $owner) => $owner
                ->where('account_type', AccountTypeEnum::Store)
                ->where('status', UserStatusEnum::Active))
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true))
            ->firstOrFail();
    }

    public function ensureActiveOwnerForChat(int $ownerId): int
    {
        $activeOwnerId = $this->query()
            ->select(['owner_id'])
            ->where('owner_id', $ownerId)
            ->whereHas('owner', fn (Builder $owner) => $owner
                ->where('account_type', AccountTypeEnum::Store)
                ->where('status', UserStatusEnum::Active))
            ->value('owner_id');

        if ($activeOwnerId === null) {
            throw (new ModelNotFoundException)
                ->setModel($this->getModel(), $ownerId);
        }

        return (int) $activeOwnerId;
    }

    public function ownerIdForVisibleStore(int $id): int
    {
        return (int) $this->query()
            ->select(['owner_id'])
            ->whereKey($id)
            ->whereHas('owner', fn (Builder $owner) => $owner
                ->where('account_type', AccountTypeEnum::Store)
                ->where('status', UserStatusEnum::Active))
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true))
            ->firstOrFail()
            ->owner_id;
    }

    public function findDetailsForUser(
        int $storeId,
        ?float $latitude,
        ?float $longitude,
        string $locale,
        int $userId,
    ): Model {
        $query = $this->query()
            ->select([
                'stores.id',
                'stores.owner_id',
                'stores.category_id',
                'stores.description',
                'stores.cover_image',
                'stores.latitude',
                'stores.longitude',
                'stores.is_featured',
            ])
            ->whereKey($storeId)
            ->whereHas('owner', fn (Builder $owner) => $owner
                ->where('account_type', AccountTypeEnum::Store)
                ->where('status', UserStatusEnum::Active))
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true))
            ->withExists(['favorites as is_favorite' => fn (Builder $favorite) => $favorite
                ->where('user_id', $userId)])
            ->with([
                'owner:id,name,phone_code,phone,avatar,governorate_id,city_id,address_line',
                'owner.governorate' => fn ($governorate) => $governorate
                    ->select(['id'])
                    ->where('is_active', true)
                    ->with(['translations' => fn ($translation) => $translation
                        ->select(['id', 'governorate_id', 'locale', 'name'])
                        ->where('locale', $locale)]),
                'owner.city' => fn ($city) => $city
                    ->select(['id', 'governorate_id'])
                    ->where('is_active', true)
                    ->with(['translations' => fn ($translation) => $translation
                        ->select(['id', 'city_id', 'locale', 'name'])
                        ->where('locale', $locale)]),
                'category' => fn ($category) => $category
                    ->select(['id'])
                    ->with(['translations' => fn ($translation) => $translation
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
                'products_count' => Product::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('products.store_id', 'stores.owner_id')
                    ->where('products.status', ProductStatusEnum::Published->value)
                    ->where('products.stock_quantity', '>', 0),
            ]);

        if ($latitude !== null && $longitude !== null) {
            $query->selectRaw(
                '6371 * 2 * ASIN(LEAST(1, SQRT(POWER(SIN(RADIANS(stores.latitude - ?) / 2), 2) + COS(RADIANS(?)) * COS(RADIANS(stores.latitude)) * POWER(SIN(RADIANS(stores.longitude - ?) / 2), 2)))) AS distance_km',
                [$latitude, $latitude, $longitude],
            );
        }

        return $query->firstOrFail();
    }

    public function listNearbyForUser(
        array $filters,
        UserStoreSortEnum $sort,
        ?float $latitude,
        ?float $longitude,
        string $locale,
        int $userId,
    ): Builder {
        $query = $this->query()
            ->select([
                'stores.id',
                'stores.owner_id',
                'stores.category_id',
                'stores.cover_image',
                'stores.latitude',
                'stores.longitude',
                'stores.is_featured',
            ])
            ->whereHas('owner', function (Builder $owner) use ($filters): void {
                $owner
                    ->where('account_type', AccountTypeEnum::Store)
                    ->where('status', UserStatusEnum::Active)
                    ->when(isset($filters['governorate_id']), fn (Builder $builder) => $builder->where('governorate_id', (int) $filters['governorate_id']))
                    ->when(isset($filters['search']) && trim($filters['search']) !== '', fn (Builder $builder) => $builder->where('name', 'like', '%'.trim($filters['search']).'%'));
            })
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true))
            ->when(isset($filters['main_category_id']), fn (Builder $builder) => $builder->where('category_id', (int) $filters['main_category_id']))
            ->withExists(['favorites as is_favorite' => fn (Builder $favorite) => $favorite
                ->where('user_id', $userId)])
            ->with([
                'owner:id,name,avatar,address_line',
                'category' => fn ($category) => $category->select(['id'])
                    ->with(['translations' => fn ($translation) => $translation
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

        if ($sort === UserStoreSortEnum::Nearest && $latitude !== null && $longitude !== null) {
            // Haversine distance in kilometres; all external values use bound parameters.
            $query->whereNotNull('stores.latitude')
                ->whereNotNull('stores.longitude')
                ->selectRaw(
                    '6371 * 2 * ASIN(LEAST(1, SQRT(POWER(SIN(RADIANS(stores.latitude - ?) / 2), 2) + COS(RADIANS(?)) * COS(RADIANS(stores.latitude)) * POWER(SIN(RADIANS(stores.longitude - ?) / 2), 2)))) AS distance_km',
                    [$latitude, $latitude, $longitude],
                );
        }

        return match ($sort) {
            UserStoreSortEnum::Nearest => $query
                ->orderBy('distance_km')
                ->orderByDesc('average_rating')
                ->orderByDesc('reviews_count')
                ->orderByDesc('stores.is_featured')
                ->orderByDesc('stores.id'),
            UserStoreSortEnum::Newest => $query->orderByDesc('stores.id'),
            UserStoreSortEnum::HighestRated => $query
                ->orderByDesc('average_rating')
                ->orderByDesc('reviews_count')
                ->orderByDesc('stores.is_featured')
                ->orderByDesc('stores.id'),
        };
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

    /** @param array<string, mixed> $data */
    public function updateOrCreateForOwner(int $ownerId, array $data): Model
    {
        $record = $this->query()->firstOrNew(['owner_id' => $ownerId]);

        if (array_key_exists('cover_image', $data)) {
            $data['cover_image'] = ImageHelper::upload($data['cover_image'], 'stores/covers', $record->cover_image);
        }

        $record->fill(['owner_id' => $ownerId, ...$data]);
        $record->save();

        return $this->query()->with('owner')->findOrFail($record->getKey());
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}

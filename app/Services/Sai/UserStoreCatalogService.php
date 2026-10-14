<?php

namespace App\Services\Sai;

use App\Enums\UserStoreSortEnum;
use App\Repositories\Sai\AdRepository;
use App\Repositories\Sai\ProductRepository;
use App\Repositories\Sai\StorePolicyVersionRepository;
use App\Repositories\Sai\StoreRepository;
use Illuminate\Database\Eloquent\Builder;

class UserStoreCatalogService
{
    public function __construct(
        private readonly StoreRepository $stores,
        private readonly StorePolicyVersionRepository $policies,
        private readonly ProductRepository $products,
        private readonly AdRepository $ads,
    ) {}

    /** @param array<string, mixed> $filters */
    public function nearby(array $filters, string $locale, int $userId): Builder
    {
        $latitude = isset($filters['lat']) ? (float) $filters['lat'] : null;
        $longitude = isset($filters['long']) ? (float) $filters['long'] : null;
        $sort = isset($filters['sort'])
            ? UserStoreSortEnum::from($filters['sort'])
            : ($latitude !== null && $longitude !== null ? UserStoreSortEnum::Nearest : UserStoreSortEnum::HighestRated);

        return $this->stores->listNearbyForUser($filters, $sort, $latitude, $longitude, $locale, $userId);
    }

    /** @param array<string, mixed> $filters */
    public function products(int $storeId, array $filters, string $locale, int $userId): Builder
    {
        $storeOwnerId = $this->stores->ownerIdForVisibleStore($storeId);

        return $this->products->listForVisibleUserStore($storeOwnerId, $filters, $locale, $userId);
    }

    public function activeAds(int $storeId): Builder
    {
        $this->stores->ensureVisibleForUser($storeId);

        return $this->ads->listActiveForUserStore($storeId);
    }

    /** @return array<string, mixed> */
    public function details(int $storeId, array $filters, string $locale, int $userId): array
    {
        $latitude = isset($filters['lat']) ? (float) $filters['lat'] : null;
        $longitude = isset($filters['long']) ? (float) $filters['long'] : null;
        $store = $this->stores->findDetailsForUser($storeId, $latitude, $longitude, $locale, $userId);

        return [
            'store' => $store,
            'policy' => $this->policies->currentForStore((int) $store->owner_id),
            'products' => $this->products->featuredForUserStore((int) $store->owner_id, $locale, $userId),
        ];
    }
}

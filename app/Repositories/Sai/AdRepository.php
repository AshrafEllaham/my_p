<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Ad;
use App\Models\Sai\AdDailyMetric;
use App\Models\Sai\AdPackage;
use App\Models\Sai\Product;
use App\Models\Sai\Store;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AdRepository extends MainRepository
{
    public function __construct(Ad $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function listForStore(int $storeId, array $filters): Builder
    {
        return $this->query()->select([
            'id', 'public_id', 'store_id', 'ad_package_id', 'product_id', 'category_id', 'title',
            'action_label', 'caption', 'placement', 'action', 'media_type', 'media_path', 'status',
            'cost', 'currency', 'starts_at', 'ends_at', 'created_at',
        ])->where('store_id', $storeId)
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(isset($filters['placement']), fn (Builder $query) => $query->where('placement', $filters['placement']))
            ->when(isset($filters['search']), fn (Builder $query) => $query->where('title', 'like', '%'.trim($filters['search']).'%'))
            ->orderByDesc('created_at')->orderByDesc('id');
    }

    public function findForStore(int|string $id, int $storeId): Model
    {
        return $this->query()->where('store_id', $storeId)->findOrFail($id);
    }

    public function findDetailsForStore(int|string $id, int $storeId): Model
    {
        $ad = $this->query()
            ->select([
                'id', 'public_id', 'store_id', 'ad_package_id', 'product_id', 'category_id', 'title',
                'action_label', 'caption', 'placement', 'action', 'media_type', 'media_path', 'status',
                'cost', 'currency', 'starts_at', 'ends_at', 'created_at',
            ])
            ->with([
                'adPackage:id,code,duration_days,price,currency',
                'adPackage.translations',
                'product:id,name',
                'category:id',
                'category.translations',
            ])
            ->where('store_id', $storeId)
            ->findOrFail($id);

        $fromDate = now()->subDays(6)->toDateString();
        $dailyMetrics = AdDailyMetric::query()
            ->select(['metric_date', 'impressions', 'clicks', 'chats_started', 'orders_attributed', 'revenue_attributed'])
            ->where('ad_id', $ad->getKey())
            ->where('metric_date', '>=', $fromDate)
            ->orderBy('metric_date')
            ->get();

        $ad->setRelation('dailyMetrics', $dailyMetrics);
        $ad->setAttribute('metrics_summary', [
            'impressions' => (int) $dailyMetrics->sum('impressions'),
            'clicks' => (int) $dailyMetrics->sum('clicks'),
            'chats_started' => (int) $dailyMetrics->sum('chats_started'),
            'orders_attributed' => (int) $dailyMetrics->sum('orders_attributed'),
            'revenue_attributed' => number_format((float) $dailyMetrics->sum('revenue_attributed'), 2, '.', ''),
        ]);

        return $ad;
    }

    public function activePackage(int $id): ?AdPackage
    {
        return AdPackage::query()
            ->select(['id', 'price', 'currency'])
            ->whereKey($id)
            ->where('is_active', true)
            ->first();
    }

    /** @return Collection<int, AdPackage> */
    public function activePackages(): Collection
    {
        return AdPackage::query()
            ->select(['id', 'code', 'duration_days', 'price', 'currency'])
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('duration_days')
            ->get();
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

    public function productBelongsToStore(int $productId, int $storeId): bool
    {
        return Product::query()->whereKey($productId)->where('store_id', $storeId)->exists();
    }

    public function createForStore(array $data): Model
    {
        return $this->getModel()->newQuery()->create($data);
    }

    public function updateForStore(int|string $id, int $storeId, array $data): Model
    {
        $ad = $this->findForStore($id, $storeId);
        $ad->fill($data);
        $ad->save();

        return $ad->refresh();
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

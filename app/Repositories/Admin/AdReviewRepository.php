<?php

namespace App\Repositories\Admin;

use App\Enums\AdStatusEnum;
use App\Models\Sai\Ad;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class AdReviewRepository extends MainRepository
{
    public function __construct(Ad $model)
    {
        $this->model = $model;
    }

    public function pendingQuery(): Builder
    {
        return $this->getModel()->newQuery()
            ->join('stores', 'stores.id', '=', 'ads.store_id')
            ->join('users', 'users.id', '=', 'stores.owner_id')
            ->where('ads.status', AdStatusEnum::PendingReview)
            ->select([
                'ads.id',
                'ads.title',
                'ads.placement',
                'ads.cost',
                'ads.currency',
                'ads.created_at',
                'users.name as store_owner_name',
            ]);
    }

    public function pendingCount(): int
    {
        return $this->getModel()->newQuery()
            ->where('status', AdStatusEnum::PendingReview)
            ->count();
    }

    public function findPendingForReview(int $id): Ad
    {
        /** @var Ad $ad */
        $ad = $this->getModel()->newQuery()
            ->with([
                'store:id,owner_id',
                'store.owner:id,name',
                'product:id,name',
                'category.translations',
                'adPackage:id,duration_days',
            ])
            ->where('status', AdStatusEnum::PendingReview)
            ->findOrFail($id);

        return $ad;
    }

    public function lockPendingForReview(int $id): Ad
    {
        /** @var Ad $ad */
        $ad = $this->getModel()->newQuery()
            ->with('adPackage:id,duration_days')
            ->where('status', AdStatusEnum::PendingReview)
            ->lockForUpdate()
            ->findOrFail($id);

        return $ad;
    }

    /** @param array<string, mixed> $data */
    public function updateReview(Ad $ad, array $data): Ad
    {
        $ad->fill($data);
        $ad->save();

        return $ad->refresh();
    }
}

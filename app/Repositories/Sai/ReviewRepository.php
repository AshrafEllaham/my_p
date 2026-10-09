<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Enums\StoreReviewPeriodEnum;
use App\Models\Sai\Review;
use App\Models\Sai\User;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReviewRepository extends MainRepository
{
    public function __construct(Review $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    /** @param array<string, mixed> $filters */
    public function listForProduct(int $storeId, int $productId, array $filters): Builder
    {
        return $this->query()
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('is_visible', true)
            ->select(['id', 'user_id', 'product_id', 'rating', 'comment', 'created_at'])
            ->with([
                'user:id,name',
                'product:id,name',
            ])
            ->when(isset($filters['rating']), fn (Builder $query) => $query->where('rating', $filters['rating']))
            ->when(isset($filters['period']), function (Builder $query) use ($filters): void {
                $since = match (StoreReviewPeriodEnum::from($filters['period'])) {
                    StoreReviewPeriodEnum::Month => now()->subDays(30),
                    StoreReviewPeriodEnum::Week => now()->startOfWeek(),
                    StoreReviewPeriodEnum::All => null,
                };

                if ($since) {
                    $query->where('created_at', '>=', $since);
                }
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /** @return array{total_reviews: int, average_rating: float|null, rating_distribution: array<int, array{rating: int, total: int}>, customer_satisfaction_percentage: int, new_this_month_count: int} */
    public function summaryForProduct(int $storeId, int $productId): array
    {
        $summary = $this->query()
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('is_visible', true)
            ->selectRaw('COUNT(*) AS total_reviews')
            ->selectRaw('AVG(rating) AS average_rating')
            ->selectRaw('COALESCE(SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END), 0) AS positive_reviews')
            ->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) AS new_this_month_count', [now()->startOfMonth()])
            ->first();

        $distribution = $this->query()
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('is_visible', true)
            ->selectRaw('rating, COUNT(*) AS total')
            ->groupBy('rating')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->rating => (int) $row->total])
            ->all();
        $total = (int) $summary->total_reviews;

        return [
            'total_reviews' => $total,
            'average_rating' => $summary->average_rating === null ? null : round((float) $summary->average_rating, 1),
            'rating_distribution' => array_map(
                fn (int $rating): array => ['rating' => $rating, 'total' => $distribution[$rating] ?? 0],
                range(1, 5),
            ),
            'customer_satisfaction_percentage' => $total > 0 ? (int) round(((int) $summary->positive_reviews / $total) * 100) : 0,
            'new_this_month_count' => (int) $summary->new_this_month_count,
        ];
    }

    public function isStoreOwner(int $storeId): bool
    {
        return User::query()
            ->whereKey($storeId)
            ->where('account_type', AccountTypeEnum::Store)
            ->exists();
    }

    public function productBelongsToStore(int $productId, int $storeId): bool
    {
        return $this->query()
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->exists();
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

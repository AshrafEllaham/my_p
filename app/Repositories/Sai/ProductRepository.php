<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\ProductOrderByEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ProductStockFilterEnum;
use App\Models\Sai\AdDailyMetric;
use App\Models\Sai\OrderItem;
use App\Models\Sai\Product;
use App\Models\Sai\Review;
use App\Models\Sai\User;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductRepository extends MainRepository
{
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    /** @param array<string, mixed> $filters */
    public function listForStore(int $storeId, array $filters, string $locale): Builder
    {
        $query = $this->query()
            ->where('store_id', $storeId)
            ->select([
                'id', 'store_id', 'category_id', 'name', 'description', 'sku', 'slug',
                'status', 'price', 'original_price', 'discount_percentage', 'discount_ends_at',
                'stock_quantity', 'low_stock_threshold', 'published_at',
            ])
            ->with([
                'media:id,product_id,type,path,is_primary',
                'features:id,product_id,name',
                'category' => fn ($categoryQuery) => $categoryQuery
                    ->select(['id', 'parent_id'])
                    ->with(['translations' => fn ($translationQuery) => $translationQuery
                        ->select(['id', 'category_id', 'locale', 'name'])
                        ->where('locale', $locale)]),
            ])
            ->when(isset($filters['status']), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(isset($filters['category_id']), fn (Builder $builder) => $builder->where('category_id', $filters['category_id']))
            ->when(isset($filters['search']), function (Builder $builder) use ($filters, $locale): void {
                $search = trim($filters['search']);
                if ($search === '') {
                    return;
                }

                $builder->where(function (Builder $searchQuery) use ($search, $locale): void {
                    $searchQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhereHas('category.translations', fn (Builder $translationQuery) => $translationQuery
                            ->where('locale', $locale)
                            ->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when(isset($filters['stock_status']), function (Builder $builder) use ($filters): void {
                match (ProductStockFilterEnum::from($filters['stock_status'])) {
                    ProductStockFilterEnum::All => null,
                    ProductStockFilterEnum::Available => $builder->whereColumn('stock_quantity', '>', 'low_stock_threshold'),
                    ProductStockFilterEnum::Low => $builder->where('stock_quantity', '>', 0)
                        ->whereColumn('stock_quantity', '<=', 'low_stock_threshold'),
                    ProductStockFilterEnum::Out => $builder->where('stock_quantity', 0),
                };
            });

        match (ProductOrderByEnum::from($filters['order_by'] ?? ProductOrderByEnum::Recent->value)) {
            ProductOrderByEnum::Recent => null,
            ProductOrderByEnum::MostOrdered => $query
                ->addSelect(['ordered_units' => OrderItem::query()
                    ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('order_items.product_id', 'products.id')
                    ->whereNotIn('orders.status', [OrderStatusEnum::Cancelled->value, OrderStatusEnum::Refunded->value])]),
            ProductOrderByEnum::MostViewed => $query
                ->addSelect(['ad_impressions' => AdDailyMetric::query()
                    ->selectRaw('COALESCE(SUM(ad_daily_metrics.impressions), 0)')
                    ->join('ads', 'ads.id', '=', 'ad_daily_metrics.ad_id')
                    ->whereColumn('ads.product_id', 'products.id')
                    ->whereNull('ads.deleted_at')]),
        };

        if (($filters['order_by'] ?? ProductOrderByEnum::Recent->value) === ProductOrderByEnum::MostOrdered->value) {
            $query->orderByDesc('ordered_units');
        } elseif (($filters['order_by'] ?? null) === ProductOrderByEnum::MostViewed->value) {
            $query->orderByDesc('ad_impressions');
        }

        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function findDetailsForStore(int|string $productId, int $storeId, string $locale): Model
    {
        return $this->query()
            ->where('store_id', $storeId)
            ->with([
                'media:id,product_id,type,path,is_primary',
                'features:id,product_id,name',
                'category' => fn ($categoryQuery) => $categoryQuery
                    ->select(['id', 'parent_id'])
                    ->with(['translations' => fn ($translationQuery) => $translationQuery
                        ->select(['id', 'category_id', 'locale', 'name'])
                        ->where('locale', $locale)]),
            ])
            ->findOrFail($productId);
    }

    /** @return array<string, mixed> */
    public function analyticsForProduct(int|string $productId): array
    {
        $thirtyDaysAgo = now()->subDays(29)->startOfDay();
        $sales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
            ->where('orders.status', OrderStatusEnum::Completed->value)
            ->selectRaw('COALESCE(SUM(order_items.line_total), 0) AS total_revenue')
            ->selectRaw('COUNT(DISTINCT orders.id) AS completed_orders_count')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) AS units_sold')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.created_at >= ? THEN order_items.line_total ELSE 0 END), 0) AS revenue_last_30_days',
                [$thirtyDaysAgo],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN order_items.created_at >= ? THEN order_items.quantity ELSE 0 END), 0) AS units_sold_last_30_days',
                [$thirtyDaysAgo],
            )
            ->first();

        $dailySalesByDate = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
            ->where('orders.status', OrderStatusEnum::Completed->value)
            ->where('order_items.created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(order_items.created_at) AS sale_date')
            ->selectRaw('COALESCE(SUM(order_items.line_total), 0) AS revenue')
            ->groupByRaw('DATE(order_items.created_at)')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');
        $dailySales = array_map(function (int $dayOffset) use ($dailySalesByDate, $thirtyDaysAgo): array {
            $date = $thirtyDaysAgo->copy()->addDays($dayOffset)->toDateString();

            return [
                'date' => $date,
                'revenue' => (float) ($dailySalesByDate->get($date)?->revenue ?? 0),
            ];
        }, range(0, 29));

        $adMetrics = AdDailyMetric::query()
            ->join('ads', 'ads.id', '=', 'ad_daily_metrics.ad_id')
            ->where('ads.product_id', $productId)
            ->whereNull('ads.deleted_at')
            ->selectRaw('COALESCE(SUM(ad_daily_metrics.impressions), 0) AS impressions')
            ->selectRaw('COALESCE(SUM(ad_daily_metrics.clicks), 0) AS clicks')
            ->selectRaw('COALESCE(SUM(ad_daily_metrics.chats_started), 0) AS chats_started')
            ->selectRaw('COALESCE(SUM(ad_daily_metrics.orders_attributed), 0) AS orders_attributed')
            ->first();

        $reviewSummary = Review::query()
            ->where('product_id', $productId)
            ->where('is_visible', true)
            ->selectRaw('COUNT(*) AS total_reviews')
            ->selectRaw('AVG(rating) AS average_rating')
            ->first();

        $ratingDistribution = Review::query()
            ->where('product_id', $productId)
            ->where('is_visible', true)
            ->selectRaw('rating, COUNT(*) AS total')
            ->groupBy('rating')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->rating => (int) $row->total])
            ->all();
        $ratingDistribution = array_map(
            fn (int $rating): array => ['rating' => $rating, 'total' => $ratingDistribution[$rating] ?? 0],
            range(1, 5),
        );

        $latestReview = Review::query()
            ->where('product_id', $productId)
            ->where('is_visible', true)
            ->with('user:id,name')
            ->select(['id', 'user_id', 'rating', 'comment', 'created_at'])
            ->latest('created_at')
            ->first();

        return [
            'total_revenue' => (float) $sales->total_revenue,
            'completed_orders_count' => (int) $sales->completed_orders_count,
            'units_sold' => (int) $sales->units_sold,
            'revenue_last_30_days' => (float) $sales->revenue_last_30_days,
            'units_sold_last_30_days' => (int) $sales->units_sold_last_30_days,
            'daily_sales' => $dailySales,
            'ad_metrics' => [
                'impressions' => (int) $adMetrics->impressions,
                'clicks' => (int) $adMetrics->clicks,
                'chats_started' => (int) $adMetrics->chats_started,
                'orders_attributed' => (int) $adMetrics->orders_attributed,
                'conversion_rate' => (int) $adMetrics->impressions > 0
                    ? round(((int) $adMetrics->orders_attributed / (int) $adMetrics->impressions) * 100, 2)
                    : null,
            ],
            'total_reviews' => (int) $reviewSummary->total_reviews,
            'average_rating' => $reviewSummary->average_rating === null ? null : (float) $reviewSummary->average_rating,
            'rating_distribution' => $ratingDistribution,
            'latest_review' => $latestReview,
        ];
    }

    /** @return array{total_products_count: int, total_new_products_this_month_count: int, total_published_products_count: int, low_stock_products_count: int} */
    public function summaryForStore(int $storeId): array
    {
        $summary = $this->query()
            ->where('store_id', $storeId)
            ->selectRaw('COUNT(*) AS total_products_count')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) AS total_new_products_this_month_count',
                [now()->startOfMonth()],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS total_published_products_count',
                [ProductStatusEnum::Published->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= low_stock_threshold THEN 1 ELSE 0 END), 0) AS low_stock_products_count',
            )
            ->first();

        return [
            'total_products_count' => (int) $summary->total_products_count,
            'total_new_products_this_month_count' => (int) $summary->total_new_products_this_month_count,
            'total_published_products_count' => (int) $summary->total_published_products_count,
            'low_stock_products_count' => (int) $summary->low_stock_products_count,
        ];
    }

    public function isStoreOwner(int $storeId): bool
    {
        return User::query()
            ->whereKey($storeId)
            ->where('account_type', AccountTypeEnum::Store)
            ->exists();
    }

    public function findWithMediaAndFeatures(int|string $id): Model
    {
        return $this->query()->with(['media', 'features'])->findOrFail($id);
    }

    public function findForStoreWithMediaAndFeatures(int|string $id, int $storeId): Model
    {
        return $this->query()
            ->where('store_id', $storeId)
            ->with(['media', 'features'])
            ->findOrFail($id);
    }

    public function lockForStoreWithMediaAndFeatures(int|string $id, int $storeId): Model
    {
        return $this->query()
            ->where('store_id', $storeId)
            ->with(['media', 'features'])
            ->lockForUpdate()
            ->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function updateForStore(int|string $id, int $storeId, array $data): Model
    {
        $product = $this->query()->where('store_id', $storeId)->findOrFail($id);
        $product->fill($data);
        $product->save();

        return $this->findForStoreWithMediaAndFeatures($product->getKey(), $storeId);
    }

    public function deleteForStore(int|string $id, int $storeId): bool
    {
        return (bool) $this->query()->where('store_id', $storeId)->findOrFail($id)->delete();
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

<?php

namespace App\Repositories\Sai;

use App\Enums\AccountTypeEnum;
use App\Enums\CouponListStatusEnum;
use App\Enums\CouponStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Sai\Coupon;
use App\Models\Sai\CouponRedemption;
use App\Models\Sai\Order;
use App\Models\Sai\Store;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CouponRepository extends MainRepository
{
    public function __construct(Coupon $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    /** @return array<string, mixed>|null */
    public function checkoutSnapshotForUpdate(string $code, int $storeOwnerId): ?array
    {
        $coupon = $this->query()
            ->select([
                'id', 'store_id', 'code', 'discount_type', 'discount_value', 'minimum_order_amount',
                'maximum_discount_amount', 'max_uses', 'max_uses_per_user', 'uses_count', 'status',
                'starts_at', 'expires_at',
            ])
            ->where('code', mb_strtoupper($code))
            ->whereHas('store', fn (Builder $query) => $query->where('owner_id', $storeOwnerId))
            ->lockForUpdate()
            ->first();

        if ($coupon === null) {
            return null;
        }

        return [
            'id' => (int) $coupon->getKey(),
            'store_id' => (int) $coupon->store_id,
            'code' => (string) $coupon->code,
            'discount_type' => $coupon->discount_type->value,
            'discount_value' => (string) $coupon->discount_value,
            'minimum_order_amount' => (string) $coupon->minimum_order_amount,
            'maximum_discount_amount' => $coupon->maximum_discount_amount === null ? null : (string) $coupon->maximum_discount_amount,
            'max_uses' => $coupon->max_uses === null ? null : (int) $coupon->max_uses,
            'max_uses_per_user' => (int) $coupon->max_uses_per_user,
            'uses_count' => (int) $coupon->uses_count,
            'status' => $coupon->status->value,
            'starts_at' => $coupon->starts_at?->toIso8601String(),
            'expires_at' => $coupon->expires_at?->toIso8601String(),
        ];
    }

    public function incrementUsesCount(int $couponId): void
    {
        $this->query()->whereKey($couponId)->increment('uses_count');
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

    /** @return Builder<Coupon> */
    public function listForStore(int $storeId, array $filters = []): Builder
    {
        return $this->query()->select([
            'id', 'store_id', 'code', 'discount_type', 'discount_value', 'minimum_order_amount',
            'maximum_discount_amount', 'max_uses', 'max_uses_per_user', 'uses_count', 'status',
            'starts_at', 'expires_at', 'created_at',
        ])->where('store_id', $storeId)
            ->when(isset($filters['status']), function (Builder $query) use ($filters): void {
                $status = CouponListStatusEnum::from($filters['status']);

                match ($status) {
                    CouponListStatusEnum::Active => $query->where('status', CouponStatusEnum::Active)
                        ->where(function (Builder $query): void {
                            $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                        })
                        ->where(function (Builder $query): void {
                            $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                        })
                        ->where(function (Builder $query): void {
                            $query->whereNull('max_uses')->orWhereColumn('uses_count', '<', 'max_uses');
                        }),
                    CouponListStatusEnum::Inactive => $query->where('status', CouponStatusEnum::Paused),
                    CouponListStatusEnum::Expired => $query->where(function (Builder $query): void {
                        $query->whereIn('status', [
                            CouponStatusEnum::Expired->value,
                            CouponStatusEnum::Exhausted->value,
                        ])
                            ->orWhere('expires_at', '<', now());
                    }),
                };
            })
            ->when(isset($filters['search']), fn (Builder $query) => $query->where('code', 'like', '%'.trim($filters['search']).'%'))
            ->orderByDesc('created_at')->orderByDesc('id');
    }

    /** @return array{active_coupons_count: int, total_uses_count: int} */
    public function summaryForStore(int $storeId): array
    {
        return [
            'active_coupons_count' => $this->listForStore($storeId, [
                'status' => CouponListStatusEnum::Active->value,
            ])->count(),
            'total_uses_count' => (int) $this->query()
                ->where('store_id', $storeId)
                ->sum('uses_count'),
        ];
    }

    public function findForStore(int|string $id, int $storeId): Model
    {
        return $this->query()->where('store_id', $storeId)->findOrFail($id);
    }

    /** @return array{completed_orders_count: int, sales_amount: float, discount_amount: float, average_order_amount: float, usage_last_7_days: array<int, array{date: string, count: int}>} */
    public function statisticsForStoreCoupon(int $couponId, int $storeId): array
    {
        $storeOwnerId = (int) Store::query()->whereKey($storeId)->value('owner_id');
        $completedOrders = Order::query()
            ->where('coupon_id', $couponId)
            ->where('store_id', $storeOwnerId)
            ->where('status', OrderStatusEnum::Completed)
            ->where('payment_status', PaymentStatusEnum::Paid);

        $orderStatistics = (clone $completedOrders)
            ->selectRaw('COUNT(*) as completed_orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as sales_amount')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(AVG(total_amount), 0) as average_order_amount')
            ->first();

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);
        $usageCounts = CouponRedemption::query()
            ->where('coupon_id', $couponId)
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as usage_date, COUNT(*) as usage_count')
            ->groupBy('usage_date')
            ->pluck('usage_count', 'usage_date');

        $usageLast7Days = [];
        for ($date = $startDate->copy(); $date->lte($today); $date->addDay()) {
            $key = $date->toDateString();
            $usageLast7Days[] = ['date' => $key, 'count' => (int) ($usageCounts[$key] ?? 0)];
        }

        return [
            'completed_orders_count' => (int) $orderStatistics->completed_orders_count,
            'sales_amount' => (float) $orderStatistics->sales_amount,
            'discount_amount' => (float) $orderStatistics->discount_amount,
            'average_order_amount' => (float) $orderStatistics->average_order_amount,
            'usage_last_7_days' => $usageLast7Days,
        ];
    }

    public function findForStoreForUpdate(int|string $id, int $storeId): Model
    {
        return $this->query()->where('store_id', $storeId)->lockForUpdate()->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createForStore(array $data): Model
    {
        return $this->getModel()->newQuery()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function updateForStore(int|string $id, int $storeId, array $data): Model
    {
        $coupon = $this->findForStore($id, $storeId);
        $coupon->fill($data);
        $coupon->save();

        return $coupon->refresh();
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

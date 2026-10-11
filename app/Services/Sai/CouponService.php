<?php

namespace App\Services\Sai;

use App\Enums\CouponDiscountTypeEnum;
use App\Enums\CouponStatusEnum;
use App\Repositories\Sai\CouponRepository;
use App\Services\Sai\Results\CouponDetailsResult;
use App\Services\Sai\Results\CouponListResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function __construct(private readonly CouponRepository $repository) {}

    private function storeIdForOwner(int $ownerId): int
    {
        if (! $this->repository->isStoreOwner($ownerId)) {
            throw ValidationException::withMessages([
                'account_type' => [__('messages.profile.store_type_required')],
            ]);
        }

        return $this->repository->storeIdForOwner($ownerId);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listForStore(int $ownerId, array $filters = []): CouponListResult
    {
        $storeId = $this->storeIdForOwner($ownerId);

        return new CouponListResult(
            $this->repository->listForStore($storeId, $filters),
            $this->repository->summaryForStore($storeId),
        );
    }

    public function detailsForStore(int $ownerId, int|string $couponId): CouponDetailsResult
    {
        $storeId = $this->storeIdForOwner($ownerId);
        $coupon = $this->repository->findForStore($couponId, $storeId);

        return new CouponDetailsResult(
            $coupon,
            $this->repository->statisticsForStoreCoupon((int) $coupon->id, $storeId),
        );
    }

    /** @param array<string, mixed> $data */
    public function createForStore(int $ownerId, array $data): Model
    {
        $storeId = $this->storeIdForOwner($ownerId);
        $isActive = $data['is_active'] ?? true;
        unset($data['is_active']);

        return $this->repository->createForStore([
            ...$data,
            'store_id' => $storeId,
            'code' => strtoupper(trim($data['code'])),
            'status' => $isActive ? CouponStatusEnum::Active : CouponStatusEnum::Paused,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateForStore(int $ownerId, int|string $couponId, array $data): Model
    {
        $storeId = $this->storeIdForOwner($ownerId);
        $current = $this->repository->findForStore($couponId, $storeId);
        $updates = $data;
        unset($updates['is_active']);

        $discountType = CouponDiscountTypeEnum::from($data['discount_type'] ?? $current->discount_type->value);
        $discountValue = $data['discount_value'] ?? $current->discount_value;
        if ($discountType === CouponDiscountTypeEnum::Percentage && (float) $discountValue > 100) {
            throw ValidationException::withMessages([
                'discount_value' => [__('messages.validation.store_coupons.discount_value.lte')],
            ]);
        }

        $maxUses = $data['max_uses'] ?? $current->max_uses;
        $perUserLimit = $data['max_uses_per_user'] ?? $current->max_uses_per_user;
        if ($maxUses !== null && $perUserLimit !== null && (int) $perUserLimit > (int) $maxUses) {
            throw ValidationException::withMessages([
                'max_uses_per_user' => [__('messages.validation.store_coupons.max_uses_per_user.lte')],
            ]);
        }

        if (array_key_exists('code', $updates)) {
            $updates['code'] = strtoupper(trim($updates['code']));
        }
        if (array_key_exists('is_active', $data)) {
            $updates['status'] = $data['is_active'] ? CouponStatusEnum::Active : CouponStatusEnum::Paused;
        }

        return $this->repository->updateForStore($couponId, $storeId, $updates);
    }

    public function toggleForStore(int $ownerId, int|string $couponId): Model
    {
        $storeId = $this->storeIdForOwner($ownerId);

        return DB::transaction(function () use ($couponId, $storeId): Model {
            $coupon = $this->repository->findForStoreForUpdate($couponId, $storeId);
            $hasExpired = $coupon->expires_at?->isPast() ?? false;
            $hasReachedUsageLimit = $coupon->max_uses !== null
                && $coupon->uses_count >= $coupon->max_uses;

            if (! in_array($coupon->status, [CouponStatusEnum::Active, CouponStatusEnum::Paused], true)
                || $hasExpired
                || $hasReachedUsageLimit) {
                throw ValidationException::withMessages([
                    'status' => [__('messages.coupons.toggle_unavailable')],
                ]);
            }

            return $this->repository->updateForStore($couponId, $storeId, [
                'status' => $coupon->status === CouponStatusEnum::Active
                    ? CouponStatusEnum::Paused
                    : CouponStatusEnum::Active,
            ]);
        });
    }

    public function deleteForStore(int $ownerId, int|string $couponId): bool
    {
        return $this->repository->deleteForStore($couponId, $this->storeIdForOwner($ownerId));
    }
}

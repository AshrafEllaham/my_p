<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\ListStoreCouponsRequest;
use App\Http\Requests\Api\Store\SaveStoreCouponRequest;
use App\Http\Resources\Api\Store\StoreCouponDetailsResource;
use App\Http\Resources\Api\Store\StoreCouponResource;
use App\Services\Sai\CouponService;
use Illuminate\Http\JsonResponse;

class StoreCouponController extends Controller
{
    public function __construct(private readonly CouponService $service) {}

    public function index(ListStoreCouponsRequest $request): JsonResponse
    {
        $result = $this->service->listForStore((int) auth('api')->id(), $request->validated());

        return generalReturn(
            $request,
            $result->query,
            StoreCouponResource::class,
            __('messages.coupons.listed'),
            ['summary' => $result->summary],
        );
    }

    public function show(int $coupon): JsonResponse
    {
        $record = $this->service->detailsForStore((int) auth('api')->id(), $coupon);

        return jsonSuccess(StoreCouponDetailsResource::make($record), __('messages.coupons.retrieved'));
    }

    public function store(SaveStoreCouponRequest $request): JsonResponse
    {
        $coupon = $this->service->createForStore((int) auth('api')->id(), $request->validated());

        return jsonSuccess(StoreCouponResource::make($coupon), __('messages.coupons.created'));
    }

    public function update(SaveStoreCouponRequest $request, int $coupon): JsonResponse
    {
        $updatedCoupon = $this->service->updateForStore(
            (int) auth('api')->id(),
            $coupon,
            $request->validated(),
        );

        return jsonSuccess(StoreCouponResource::make($updatedCoupon), __('messages.coupons.updated'));
    }

    public function toggle(int $coupon): JsonResponse
    {
        $updatedCoupon = $this->service->toggleForStore((int) auth('api')->id(), $coupon);

        return jsonSuccess(StoreCouponResource::make($updatedCoupon), __('messages.coupons.toggled'));
    }

    public function destroy(int $coupon): JsonResponse
    {
        $this->service->deleteForStore((int) auth('api')->id(), $coupon);

        return jsonSuccess(null, __('messages.coupons.deleted'));
    }
}

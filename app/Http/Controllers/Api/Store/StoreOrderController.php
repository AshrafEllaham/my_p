<?php

namespace App\Http\Controllers\Api\Store;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\ConfirmStoreOrderPickupRequest;
use App\Http\Requests\Api\Store\ListStoreOrdersRequest;
use App\Http\Requests\Api\Store\UpdateStoreOrderStatusRequest;
use App\Http\Resources\Api\Store\StoreOrderResource;
use App\Services\Sai\StoreOrderLifecycleService;
use Illuminate\Http\JsonResponse;

class StoreOrderController extends Controller
{
    public function __construct(private readonly StoreOrderLifecycleService $service) {}

    public function index(ListStoreOrdersRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->list((int) auth('api')->id(), $request->validated()),
            StoreOrderResource::class,
            __('messages.store_orders.listed'),
        );
    }

    public function show(int $order): JsonResponse
    {
        return jsonSuccess(
            StoreOrderResource::make($this->service->find((int) auth('api')->id(), $order)),
            __('messages.store_orders.retrieved'),
        );
    }

    public function updateStatus(UpdateStoreOrderStatusRequest $request, int $order): JsonResponse
    {
        $updated = $this->service->transition(
            (int) auth('api')->id(),
            $order,
            OrderStatusEnum::from($request->validated()['status']),
        );

        return jsonSuccess(StoreOrderResource::make($updated), __('messages.orders.store_status_updated'));
    }

    public function confirmPickup(ConfirmStoreOrderPickupRequest $request, int $order): JsonResponse
    {
        $updated = $this->service->confirmPickup(
            (int) auth('api')->id(),
            $order,
            $request->validated()['pickup_qr_token'],
        );

        return jsonSuccess(StoreOrderResource::make($updated), __('messages.orders.pickup_confirmed'));
    }
}

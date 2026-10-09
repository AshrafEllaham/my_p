<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\ListStoreAdsRequest;
use App\Http\Requests\Api\Store\SaveStoreAdRequest;
use App\Http\Resources\Api\Store\StoreAdDetailsResource;
use App\Http\Resources\Api\Store\StoreAdPackageResource;
use App\Http\Resources\Api\Store\StoreAdResource;
use App\Services\Sai\AdService;
use Illuminate\Http\JsonResponse;

class StoreAdController extends Controller
{
    public function __construct(private readonly AdService $service) {}

    public function index(ListStoreAdsRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->listForStore((int) auth('api')->id(), $request->validated()),
            StoreAdResource::class,
            __('messages.ads.listed'),
        );
    }

    public function show(int $ad): JsonResponse
    {
        $details = $this->service->detailsForStore((int) auth('api')->id(), $ad);

        return jsonSuccess(StoreAdDetailsResource::make($details), __('messages.ads.retrieved'));
    }

    public function packages(): JsonResponse
    {
        $packages = $this->service->activePackagesForStore((int) auth('api')->id());

        return jsonSuccess(StoreAdPackageResource::collection($packages), __('messages.ads.packages_listed'));
    }

    public function store(SaveStoreAdRequest $request): JsonResponse
    {
        $ad = $this->service->createForStore((int) auth('api')->id(), $request->validated());

        return jsonSuccess(StoreAdResource::make($ad), __('messages.ads.created'));
    }

    public function update(SaveStoreAdRequest $request, int $ad): JsonResponse
    {
        $updatedAd = $this->service->updateForStore((int) auth('api')->id(), $ad, $request->validated());

        return jsonSuccess(StoreAdResource::make($updatedAd), __('messages.ads.updated'));
    }

    public function toggle(int $ad): JsonResponse
    {
        $updatedAd = $this->service->toggleForStore((int) auth('api')->id(), $ad);

        return jsonSuccess(StoreAdResource::make($updatedAd), __('messages.ads.toggled'));
    }

    public function destroy(int $ad): JsonResponse
    {
        $this->service->deleteForStore((int) auth('api')->id(), $ad);

        return jsonSuccess(null, __('messages.ads.deleted'));
    }
}

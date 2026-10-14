<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ListNearbyStoresRequest;
use App\Http\Requests\Api\User\ListUserStoreAdsRequest;
use App\Http\Requests\Api\User\ListUserStoreProductsRequest;
use App\Http\Requests\Api\User\ShowUserStoreRequest;
use App\Http\Resources\Api\User\UserNearbyStoreResource;
use App\Http\Resources\Api\User\UserProductResource;
use App\Http\Resources\Api\User\UserStoreAdResource;
use App\Http\Resources\Api\User\UserStoreDetailsResource;
use App\Services\Sai\UserStoreCatalogService;
use Illuminate\Http\JsonResponse;

class UserStoreController extends Controller
{
    public function __construct(private readonly UserStoreCatalogService $service) {}

    public function nearby(ListNearbyStoresRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->nearby($request->validated(), app()->getLocale(), (int) auth('api')->id()),
            UserNearbyStoreResource::class,
            __('messages.user_stores.nearby_listed'),
        );
    }

    public function show(ShowUserStoreRequest $request, int $store): JsonResponse
    {
        $details = $this->service->details($store, $request->validated(), app()->getLocale(), (int) auth('api')->id());

        return jsonSuccess(UserStoreDetailsResource::make($details), __('messages.user_stores.retrieved'));
    }

    public function products(ListUserStoreProductsRequest $request, int $store): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->products($store, $request->validated(), app()->getLocale(), (int) auth('api')->id()),
            UserProductResource::class,
            __('messages.user_stores.products_listed'),
        );
    }

    public function ads(ListUserStoreAdsRequest $request, int $store): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->activeAds($store),
            UserStoreAdResource::class,
            __('messages.user_stores.ads_listed'),
        );
    }
}

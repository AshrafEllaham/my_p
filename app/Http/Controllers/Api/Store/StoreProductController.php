<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\CreateProductRequest;
use App\Http\Requests\Api\Store\ListStoreProductsRequest;
use App\Http\Requests\Api\Store\UpdateProductRequest;
use App\Http\Resources\Api\Store\StoreProductDetailsResource;
use App\Http\Resources\Api\Store\StoreProductResource;
use App\Services\Sai\StoreProductService;
use Illuminate\Http\JsonResponse;

class StoreProductController extends Controller
{
    public function __construct(private readonly StoreProductService $service) {}

    public function store(CreateProductRequest $request): JsonResponse
    {
        $product = $this->service->create((int) auth('api')->id(), $request->validated());

        return jsonSuccess(StoreProductResource::make($product), __('messages.products.created'));
    }

    public function index(ListStoreProductsRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $storeId = (int) auth('api')->id();

        return generalReturn(
            $request,
            $this->service->list($storeId, $filters, app()->getLocale()),
            StoreProductResource::class,
            __('messages.products.listed'),
            $this->service->summary($storeId),
        );
    }

    public function show(int $product): JsonResponse
    {
        $details = $this->service->details(
            (int) auth('api')->id(),
            $product,
            app()->getLocale(),
        );

        return jsonSuccess(StoreProductDetailsResource::make($details), __('messages.products.retrieved'));
    }

    public function update(UpdateProductRequest $request, int $product): JsonResponse
    {
        $updatedProduct = $this->service->update(
            (int) auth('api')->id(),
            $product,
            $request->validated(),
        );

        return jsonSuccess(StoreProductResource::make($updatedProduct), __('messages.products.updated'));
    }

    public function toggleVisibility(int $product): JsonResponse
    {
        $updatedProduct = $this->service->toggleVisibility((int) auth('api')->id(), $product);

        return jsonSuccess(StoreProductResource::make($updatedProduct), __('messages.products.visibility_toggled'));
    }

    public function destroy(int $product): JsonResponse
    {
        $this->service->delete((int) auth('api')->id(), $product);

        return jsonSuccess(null, __('messages.products.deleted'));
    }
}

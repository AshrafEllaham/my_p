<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ListSimilarProductsRequest;
use App\Http\Requests\Api\User\ListUserProductReviewsRequest;
use App\Http\Requests\Api\User\ListUserProductsRequest;
use App\Http\Requests\Api\User\ShowUserProductRequest;
use App\Http\Resources\Api\User\UserProductDetailsResource;
use App\Http\Resources\Api\User\UserProductResource;
use App\Http\Resources\Api\User\UserProductReviewResource;
use App\Services\Sai\UserProductCatalogService;
use Illuminate\Http\JsonResponse;

class UserProductController extends Controller
{
    public function __construct(private readonly UserProductCatalogService $service) {}

    public function index(ListUserProductsRequest $request): JsonResponse
    {
        $filters = $request->validated();

        return generalReturn(
            $request,
            $this->service->list($filters, app()->getLocale(), (int) auth('api')->id()),
            UserProductResource::class,
            __('messages.user_products.listed'),
        );
    }

    public function show(ShowUserProductRequest $request, int $product): JsonResponse
    {
        $details = $this->service->details($product, app()->getLocale(), (int) auth('api')->id());

        return jsonSuccess(UserProductDetailsResource::make($details), __('messages.user_products.retrieved'));
    }

    public function similar(ListSimilarProductsRequest $request, int $product): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->similar($product, $request->validated(), app()->getLocale(), (int) auth('api')->id()),
            UserProductResource::class,
            __('messages.user_products.similar_listed'),
        );
    }

    public function reviews(ListUserProductReviewsRequest $request, int $product): JsonResponse
    {
        $query = $this->service->reviews($product, $request->validated());

        return generalReturn(
            $request,
            $query,
            UserProductReviewResource::class,
            __('messages.user_products.reviews_listed'),
            ['summary' => $this->service->reviewSummary($product)],
        );
    }
}

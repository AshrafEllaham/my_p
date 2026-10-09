<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\ListStoreReviewsRequest;
use App\Http\Resources\Api\Store\StoreReviewResource;
use App\Services\Sai\ReviewService;
use Illuminate\Http\JsonResponse;

class StoreReviewController extends Controller
{
    public function __construct(private readonly ReviewService $service) {}

    public function index(ListStoreReviewsRequest $request, int $product): JsonResponse
    {
        $storeId = (int) auth('api')->id();
        $filters = $request->validated();

        return generalReturn(
            $request,
            $this->service->listForProduct($storeId, $product, $filters),
            StoreReviewResource::class,
            __('messages.reviews.listed'),
            ['summary' => $this->service->summaryForProduct($storeId, $product)],
        );
    }
}

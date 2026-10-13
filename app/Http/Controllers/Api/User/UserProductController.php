<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ListUserProductsRequest;
use App\Http\Resources\Api\User\UserProductResource;
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
}

<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Catalog\ListMainCategoriesRequest;
use App\Http\Requests\Api\Catalog\ListUserHomeSubCategoriesRequest;
use App\Http\Resources\Api\Catalog\CategoryLookupResource;
use App\Services\Sai\UserHomeCategoryService;
use Illuminate\Http\JsonResponse;

class UserHomeCategoryController extends Controller
{
    public function __construct(private readonly UserHomeCategoryService $service) {}

    public function index(ListMainCategoriesRequest $request): JsonResponse
    {
        $data = $request->validated();

        return generalReturn(
            $request,
            $this->service->mainCategories(
                app()->getLocale(),
                $data['search'] ?? null,
            ),
            CategoryLookupResource::class,
            __('messages.catalog.main_categories_listed'),
        );
    }

    public function subCategories(ListUserHomeSubCategoriesRequest $request): JsonResponse
    {
        $data = $request->validated();

        return generalReturn(
            $request,
            $this->service->subCategories(app()->getLocale(), (int) $data['main_category_id']),
            CategoryLookupResource::class,
            __('messages.catalog.sub_categories_listed'),
        );
    }
}

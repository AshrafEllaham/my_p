<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Catalog\ListCitiesRequest;
use App\Http\Requests\Api\Catalog\ListCountriesRequest;
use App\Http\Requests\Api\Catalog\ListGovernoratesRequest;
use App\Http\Requests\Api\Catalog\ListMainCategoriesRequest;
use App\Http\Requests\Api\Catalog\ListSubCategoriesRequest;
use App\Http\Resources\Api\Catalog\CategoryLookupResource;
use App\Http\Resources\Api\Catalog\CityLookupResource;
use App\Http\Resources\Api\Catalog\CountryLookupResource;
use App\Http\Resources\Api\Catalog\GovernorateLookupResource;
use App\Services\Sai\CatalogLookupService;
use Illuminate\Http\JsonResponse;

class CatalogLookupController extends Controller
{
    public function __construct(private readonly CatalogLookupService $service) {}

    public function mainCategories(ListMainCategoriesRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->mainCategories(app()->getLocale()),
            CategoryLookupResource::class,
            __('messages.catalog.main_categories_listed'),
        );
    }

    public function subCategories(ListSubCategoriesRequest $request): JsonResponse
    {
        $data = $request->validated();

        return generalReturn(
            $request,
            $this->service->subCategories(app()->getLocale(), $data['main_category_id'] ?? null),
            CategoryLookupResource::class,
            __('messages.catalog.sub_categories_listed'),
        );
    }

    public function countries(ListCountriesRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->countries(app()->getLocale()),
            CountryLookupResource::class,
            __('messages.catalog.countries_listed'),
        );
    }

    public function governorates(ListGovernoratesRequest $request): JsonResponse
    {
        $data = $request->validated();

        return generalReturn(
            $request,
            $this->service->governorates(app()->getLocale(), $data['country_id'] ?? null),
            GovernorateLookupResource::class,
            __('messages.catalog.governorates_listed'),
        );
    }

    public function cities(ListCitiesRequest $request): JsonResponse
    {
        $data = $request->validated();

        return generalReturn(
            $request,
            $this->service->cities(app()->getLocale(), $data['governorate_id'] ?? null),
            CityLookupResource::class,
            __('messages.catalog.cities_listed'),
        );
    }
}

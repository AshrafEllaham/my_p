<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListBannersRequest;
use App\Http\Resources\Api\BannerResource;
use App\Services\Sai\BannerService;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function __construct(private readonly BannerService $service) {}

    public function index(ListBannersRequest $request): JsonResponse
    {
        $data = $request->validated();
        $type = isset($data['type'])
            ? AccountTypeEnum::from($data['type'])
            : null;

        return jsonSuccess(
            BannerResource::collection($this->service->list($type)),
            __('messages.banners.listed'),
        );
    }
}

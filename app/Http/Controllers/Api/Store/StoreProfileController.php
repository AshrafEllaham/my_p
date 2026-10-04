<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Store\UpdateStoreProfileRequest;
use App\Http\Resources\Api\Store\StoreProfileResource;
use App\Services\Sai\StoreProfileService;
use Illuminate\Http\JsonResponse;

class StoreProfileController extends Controller
{
    public function __construct(private readonly StoreProfileService $service) {}

    public function update(UpdateStoreProfileRequest $request): JsonResponse
    {
        $profile = $this->service->update((int) auth('api')->id(), $request->validated());

        return jsonSuccess(
            StoreProfileResource::make($profile),
            __('messages.profile.store_updated'),
        );
    }
}

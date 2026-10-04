<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\UpdateUserProfileRequest;
use App\Http\Resources\Api\User\UserProfileResource;
use App\Services\Sai\UserProfileService;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    public function __construct(private readonly UserProfileService $service) {}

    public function update(UpdateUserProfileRequest $request): JsonResponse
    {
        $profile = $this->service->update((int) auth('api')->id(), $request->validated());

        return jsonSuccess(
            UserProfileResource::make($profile),
            __('messages.profile.user_updated'),
        );
    }
}

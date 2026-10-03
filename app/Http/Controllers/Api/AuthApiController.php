<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\RegisterUserRequest;
use App\Http\Resources\Api\User\RegisteredUserResource;
use App\Services\Sai\UserRegistrationService;
use Illuminate\Http\JsonResponse;

class AuthApiController extends Controller
{
    public function __construct(private readonly UserRegistrationService $service) {}

    public function store(RegisterUserRequest $request): JsonResponse
    {
        $user = $this->service->register($request->validated(), app()->getLocale());

        return jsonSuccess(
            RegisteredUserResource::make($user),
            __('messages.auth.registration_created'),
        );
    }
}

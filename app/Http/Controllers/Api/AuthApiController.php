<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ConfirmOtpRequest;
use App\Http\Requests\Api\User\LoginUserRequest;
use App\Http\Requests\Api\User\RegisterUserRequest;
use App\Http\Requests\Api\User\SendOtpRequest;
use App\Http\Requests\Api\User\SocialLoginRequest;
use App\Http\Resources\Api\User\RegisteredUserResource;
use App\Services\Sai\RegistrationOtpService;
use App\Services\Sai\UserAuthenticationService;
use Illuminate\Http\JsonResponse;

class AuthApiController extends Controller
{
    public function __construct(
        private readonly UserAuthenticationService $authenticationService,
        private readonly RegistrationOtpService $otpService,
    ) {}

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        return jsonSuccess(
            $this->otpService->send($request->validated()),
            __('messages.auth.otp_prepared'),
        );
    }

    public function confirmOtp(ConfirmOtpRequest $request): JsonResponse
    {
        return jsonSuccess(
            $this->otpService->confirm($request->validated()),
            __('messages.auth.otp_confirmed'),
        );
    }

    public function store(RegisterUserRequest $request): JsonResponse
    {
        $user = $this->authenticationService->register($request->validated(), app()->getLocale());
        $user->setAttribute('access_token', auth('api')->login($user));

        return jsonSuccess(
            RegisteredUserResource::make($user),
            __('messages.auth.registration_created'),
        );
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        $user = $this->authenticationService->loginWithPassword($request->validated());
        $user->setAttribute('access_token', auth('api')->login($user));

        return jsonSuccess(
            RegisteredUserResource::make($user),
            __('messages.auth.login_success'),
        );
    }

    public function socialLogin(SocialLoginRequest $request): JsonResponse
    {
        $user = $this->authenticationService->loginBySocial($request->validated());
        $user->setAttribute('access_token', auth('api')->login($user));

        return jsonSuccess(
            RegisteredUserResource::make($user),
            __('messages.auth.social_login_success'),
        );
    }
}

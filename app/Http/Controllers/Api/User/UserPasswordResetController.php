<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ConfirmPasswordResetOtpRequest;
use App\Http\Requests\Api\User\RequestPasswordResetOtpRequest;
use App\Http\Requests\Api\User\ResetUserPasswordRequest;
use App\Services\Sai\UserPasswordResetService;
use Illuminate\Http\JsonResponse;

class UserPasswordResetController extends Controller
{
    public function __construct(private readonly UserPasswordResetService $service) {}

    public function send(RequestPasswordResetOtpRequest $request): JsonResponse
    {
        return jsonSuccess($this->service->send($request->validated()), __('messages.auth.password_reset_otp_sent'));
    }

    public function confirm(ConfirmPasswordResetOtpRequest $request): JsonResponse
    {
        return jsonSuccess($this->service->confirm($request->validated()), __('messages.auth.password_reset_otp_confirmed'));
    }

    public function reset(ResetUserPasswordRequest $request): JsonResponse
    {
        return jsonSuccess($this->service->reset($request->validated()), __('messages.auth.password_reset_success'));
    }
}

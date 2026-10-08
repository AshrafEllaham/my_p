<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Services\Sai\ChangePasswordService;
use Illuminate\Http\JsonResponse;

class ChangePasswordController extends Controller
{
    public function __construct(private readonly ChangePasswordService $service) {}

    public function update(ChangePasswordRequest $request): JsonResponse
    {
        $this->service->change(auth('api')->user(), $request->validated());

        return jsonSuccess(null, __('messages.auth.password_updated'));
    }
}

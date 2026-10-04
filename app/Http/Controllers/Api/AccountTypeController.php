<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\UpdateAccountTypeRequest;
use App\Http\Resources\Api\User\AccountTypeResource;
use App\Services\Sai\AccountTypeService;
use Illuminate\Http\JsonResponse;

class AccountTypeController extends Controller
{
    public function __construct(private readonly AccountTypeService $service) {}

    public function update(UpdateAccountTypeRequest $request, int $account): JsonResponse
    {
        $user = $this->service->update($account, $request->validated());

        return jsonSuccess(
            AccountTypeResource::make($user),
            __('messages.account.type_updated'),
        );
    }
}

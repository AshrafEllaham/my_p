<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sai\User;
use App\Services\Sai\AccountSessionService;
use App\Services\Sai\FireBaseTokenService;
use App\Services\Sai\UserAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function __construct(
        private readonly UserAccountService $accounts,
        private readonly AccountSessionService $sessions,
        private FireBaseTokenService $fireBaseTokenService
    ) {}

    public function logout(Request $request): JsonResponse
    {
        $this->fireBaseTokenService->deleteWhere([
            'user_id' => loggedUser('id'),
            'token' => $request->firebase_token,
        ]);

        $this->sessions->logout((string) $request->bearerToken());
        Auth::guard('api')->logout();

        return jsonSuccess(null, __('messages.auth.logout_success'));
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = auth('api')->user();
        $this->accounts->delete($user);
        $this->sessions->logout((string) $request->bearerToken());
        Auth::guard('api')->logout();

        return jsonSuccess(null, __('messages.account.deleted'));
    }
}

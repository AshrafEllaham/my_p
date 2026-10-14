<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ListUserCompetitionsRequest;
use App\Http\Resources\Api\User\UserCompetitionResource;
use App\Services\Sai\UserCompetitionCatalogService;
use Illuminate\Http\JsonResponse;

class UserCompetitionController extends Controller
{
    public function __construct(private readonly UserCompetitionCatalogService $service) {}

    public function index(ListUserCompetitionsRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->list($request->validated()),
            UserCompetitionResource::class,
            __('messages.user_competitions.listed'),
        );
    }
}

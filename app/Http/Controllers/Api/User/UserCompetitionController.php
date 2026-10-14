<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ListUserCompetitionsRequest;
use App\Http\Requests\Api\User\StoreUserCompetitionAnswerRequest;
use App\Http\Resources\Api\User\UserCompetitionDetailsResource;
use App\Http\Resources\Api\User\UserCompetitionEntryResource;
use App\Http\Resources\Api\User\UserCompetitionResource;
use App\Services\Sai\UserCompetitionCatalogService;
use App\Services\Sai\UserCompetitionService;
use Illuminate\Http\JsonResponse;

class UserCompetitionController extends Controller
{
    public function __construct(
        private readonly UserCompetitionCatalogService $catalog,
        private readonly UserCompetitionService $service,
    ) {}

    public function index(ListUserCompetitionsRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->catalog->list($request->validated()),
            UserCompetitionResource::class,
            __('messages.user_competitions.listed'),
        );
    }

    public function show(int $competition): JsonResponse
    {
        $details = $this->service->details((int) auth('api')->id(), $competition);

        return jsonSuccess(
            UserCompetitionDetailsResource::make($details),
            __('messages.user_competitions.retrieved'),
        );
    }

    public function saveAnswer(StoreUserCompetitionAnswerRequest $request, int $competition): JsonResponse
    {
        $entry = $this->service->saveAnswer(
            (int) auth('api')->id(),
            $competition,
            $request->validated()['answer'],
        );

        return jsonSuccess(
            UserCompetitionEntryResource::make($entry),
            __('messages.user_competitions.answer_saved'),
        );
    }
}

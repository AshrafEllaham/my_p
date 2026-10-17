<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\AdInteractionEventTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ListUserAdsRequest;
use App\Http\Requests\Api\User\TrackUserAdInteractionRequest;
use App\Http\Resources\Api\User\UserAdInteractionResource;
use App\Http\Resources\Api\User\UserHomeAdResource;
use App\Services\Sai\UserAdService;
use Illuminate\Http\JsonResponse;

class UserAdController extends Controller
{
    public function __construct(private readonly UserAdService $service) {}

    public function index(ListUserAdsRequest $request): JsonResponse
    {
        return generalReturn(
            $request,
            $this->service->list($request->validated()),
            UserHomeAdResource::class,
            __('messages.user_ads.listed'),
        );
    }

    public function impression(TrackUserAdInteractionRequest $request, int $ad): JsonResponse
    {
        $interaction = $this->service->recordInteraction(
            (int) auth('api')->id(),
            $ad,
            $request->validated('event_id'),
            AdInteractionEventTypeEnum::Impression,
        );

        return jsonSuccess(
            UserAdInteractionResource::make($interaction),
            __('messages.user_ads.interaction_recorded'),
        );
    }

    public function click(TrackUserAdInteractionRequest $request, int $ad): JsonResponse
    {
        $interaction = $this->service->recordInteraction(
            (int) auth('api')->id(),
            $ad,
            $request->validated('event_id'),
            AdInteractionEventTypeEnum::Click,
        );

        return jsonSuccess(
            UserAdInteractionResource::make($interaction),
            __('messages.user_ads.interaction_recorded'),
        );
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Notification\UpdateNotificationPreferencesRequest;
use App\Http\Resources\Api\Notification\NotificationPreferenceResource;
use App\Services\Sai\NotificationPreferenceService;
use Illuminate\Http\JsonResponse;

class NotificationPreferenceController extends Controller
{
    public function __construct(private readonly NotificationPreferenceService $service) {}

    public function show(): JsonResponse
    {
        return jsonSuccess(
            NotificationPreferenceResource::make($this->service->get((int) auth('api')->id())),
            __('messages.notifications.preferences_loaded'),
        );
    }

    public function update(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        return jsonSuccess(
            NotificationPreferenceResource::make(
                $this->service->update((int) auth('api')->id(), $request->validated()),
            ),
            __('messages.notifications.preferences_updated'),
        );
    }
}

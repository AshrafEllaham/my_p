<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Notification\ListNotificationsRequest;
use App\Http\Resources\Api\Notification\NotificationResource;
use App\Services\Sai\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $service) {}

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $notifications = $this->service->paginate(
            (int) auth('api')->id(),
            (bool) ($data['unread_only'] ?? false),
            (int) ($data['per_page'] ?? 15),
        );

        return NotificationResource::collection($notifications)
            ->additional([
                'code' => 200,
                'message' => __('messages.notifications.listed'),
            ])
            ->response();
    }

    public function markAsRead(string $notification): JsonResponse
    {
        $notification = $this->service->markAsRead((int) auth('api')->id(), $notification);

        return jsonSuccess(
            NotificationResource::make($notification),
            __('messages.notifications.marked_read'),
        );
    }

    public function markAllAsRead(): JsonResponse
    {
        return jsonSuccess(
            ['updated' => $this->service->markAllAsRead((int) auth('api')->id())],
            __('messages.notifications.all_marked_read'),
        );
    }

    public function destroyAll(): JsonResponse
    {
        return jsonSuccess(
            ['deleted' => $this->service->deleteAll((int) auth('api')->id())],
            __('messages.notifications.deleted'),
        );
    }
}

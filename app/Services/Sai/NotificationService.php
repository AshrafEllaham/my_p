<?php

namespace App\Services\Sai;

use App\Repositories\Sai\NotificationRepository;

class NotificationService
{
    public function __construct(private readonly NotificationRepository $repository) {}

    public function paginate(int $userId, bool $unreadOnly, int $perPage): object
    {
        return $this->repository->paginateForUser($userId, $unreadOnly, $perPage);
    }

    public function markAsRead(int $userId, string $notificationId): object
    {
        return $this->repository->markAsReadForUser($userId, $notificationId);
    }

    public function markAllAsRead(int $userId): int
    {
        return $this->repository->markAllAsReadForUser($userId);
    }

    public function deleteAll(int $userId): int
    {
        return $this->repository->deleteAllForUser($userId);
    }
}

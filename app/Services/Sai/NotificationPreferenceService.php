<?php

namespace App\Services\Sai;

use App\Repositories\Sai\NotificationPreferenceRepository;

class NotificationPreferenceService
{
    public function __construct(private readonly NotificationPreferenceRepository $repository) {}

    public function get(int $userId): object
    {
        return $this->repository->findOrCreateForUser($userId);
    }

    /** @param array<string, bool> $data */
    public function update(int $userId, array $data): object
    {
        return $this->repository->updateForUser($userId, $data);
    }
}

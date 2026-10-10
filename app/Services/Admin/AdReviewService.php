<?php

namespace App\Services\Admin;

use App\Enums\AdStatusEnum;
use App\Models\Sai\Ad;
use App\Repositories\Admin\AdReviewRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;

class AdReviewService
{
    public function __construct(
        private readonly AdReviewRepository $repository,
        private readonly DatabaseManager $database,
    ) {}

    public function pendingQuery(): Builder
    {
        return $this->repository->pendingQuery();
    }

    public function pendingCount(): int
    {
        return $this->repository->pendingCount();
    }

    public function findPending(int $id): Ad
    {
        return $this->repository->findPendingForReview($id);
    }

    public function approve(int $id): Ad
    {
        return $this->database->transaction(function () use ($id): Ad {
            $ad = $this->repository->lockPendingForReview($id);
            $startsAt = $ad->starts_at?->isFuture() ? $ad->starts_at : now();
            $status = $startsAt->isFuture() ? AdStatusEnum::Scheduled : AdStatusEnum::Active;

            return $this->repository->updateReview($ad, [
                'status' => $status,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addDays((int) $ad->adPackage->duration_days),
                'rejection_reason' => null,
            ]);
        });
    }

    public function reject(int $id, string $reason): Ad
    {
        return $this->database->transaction(function () use ($id, $reason): Ad {
            $ad = $this->repository->lockPendingForReview($id);

            return $this->repository->updateReview($ad, [
                'status' => AdStatusEnum::Rejected,
                'rejection_reason' => trim($reason),
            ]);
        });
    }
}

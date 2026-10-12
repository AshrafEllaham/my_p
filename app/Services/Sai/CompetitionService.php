<?php

namespace App\Services\Sai;

use App\Enums\CompetitionStatusEnum;
use App\Repositories\Sai\CompetitionRepository;
use App\Services\Sai\Results\CompetitionDrawResult;
use App\Services\Sai\Results\CompetitionListResult;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CompetitionService
{
    public function __construct(
        private readonly CompetitionRepository $repository,
        private readonly FilesystemFactory $filesystem,
    ) {}

    /** @param array<string, mixed> $filters */
    public function listForStore(int $ownerId, array $filters = []): CompetitionListResult
    {
        $storeId = $this->storeIdForOwner($ownerId);

        return new CompetitionListResult(
            $this->repository->listForStore($storeId, $filters),
            $this->repository->summaryForStore($storeId),
        );
    }

    public function detailsForStore(int $ownerId, int|string $competitionId): Model
    {
        return $this->repository->findDetailsForStore($competitionId, $this->storeIdForOwner($ownerId));
    }

    /** @param array<string, mixed> $data */
    public function createForStore(int $ownerId, array $data): Model
    {
        $storeId = $this->storeIdForOwner($ownerId);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $imagePath = $image instanceof UploadedFile ? $this->storeImage($image) : null;

        if ($imagePath !== null) {
            $data['image'] = $imagePath;
        }

        try {
            return DB::transaction(fn (): Model => $this->repository->createForStore([
                ...$data,
                'public_id' => (string) Str::uuid(),
                'store_id' => $storeId,
                'content_locale' => app()->getLocale(),
            ]));
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                $this->filesystem->disk('public')->delete($imagePath);
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function updateForStore(int $ownerId, int|string $competitionId, array $data): Model
    {
        $storeId = $this->storeIdForOwner($ownerId);
        $competition = $this->repository->findForStore($competitionId, $storeId);
        $oldImage = $competition->image;
        $image = $data['image'] ?? null;
        unset($data['image']);
        $newImagePath = $image instanceof UploadedFile ? $this->storeImage($image) : null;

        if ($newImagePath !== null) {
            $data['image'] = $newImagePath;
        }

        try {
            $updated = DB::transaction(fn (): Model => $this->repository->updateForStore(
                $competitionId,
                $storeId,
                $data,
            ));
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                $this->filesystem->disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== null && $this->isStoredImagePath($oldImage)) {
            $this->filesystem->disk('public')->delete($oldImage);
        }

        return $updated;
    }

    public function drawForStore(int $ownerId, int|string $competitionId, string $correctAnswer): CompetitionDrawResult
    {
        $storeId = $this->storeIdForOwner($ownerId);
        $correctAnswer = trim($correctAnswer);
        $normalizedAnswer = $this->normalizeAnswer($correctAnswer);

        if ($normalizedAnswer === '') {
            throw ValidationException::withMessages([
                'correct_answer' => [__('messages.validation.store_competitions.correct_answer.required')],
            ]);
        }

        return DB::transaction(function () use ($storeId, $competitionId, $correctAnswer, $normalizedAnswer): CompetitionDrawResult {
            $competition = $this->repository->findForStoreForUpdate($competitionId, $storeId);

            if (in_array($competition->status, [CompetitionStatusEnum::Drawn, CompetitionStatusEnum::Cancelled], true)) {
                throw ValidationException::withMessages([
                    'competition' => [__('messages.competitions.draw_unavailable')],
                ]);
            }

            if ($competition->ends_at->isFuture()) {
                throw ValidationException::withMessages([
                    'competition' => [__('messages.competitions.draw_not_ended')],
                ]);
            }

            $eligibleCount = $this->repository->countMatchingEntries($competition->id, $normalizedAnswer);
            if ($eligibleCount === 0) {
                throw ValidationException::withMessages([
                    'correct_answer' => [__('messages.competitions.no_matching_entries')],
                ]);
            }

            $winner = $this->repository->randomMatchingEntry($competition->id, $normalizedAnswer);
            if ($winner === null) {
                throw ValidationException::withMessages([
                    'correct_answer' => [__('messages.competitions.no_matching_entries')],
                ]);
            }

            $competition = $this->repository->completeDraw(
                $competition,
                $correctAnswer,
                $normalizedAnswer,
                $winner,
            );

            return new CompetitionDrawResult($competition, $winner, $eligibleCount);
        });
    }

    public function deleteForStore(int $ownerId, int|string $competitionId): bool
    {
        return DB::transaction(fn (): bool => $this->repository->deleteForStore(
            $competitionId,
            $this->storeIdForOwner($ownerId),
        ));
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $this->filesystem->disk('public')->putFile('competitions', $image);

        if (! is_string($path)) {
            throw new RuntimeException(__('messages.competitions.image_upload_failed'));
        }

        return $path;
    }

    private function isStoredImagePath(?string $image): bool
    {
        return $image !== null && filter_var($image, FILTER_VALIDATE_URL) === false;
    }

    private function normalizeAnswer(string $answer): string
    {
        $normalized = mb_strtolower(trim($answer));
        $normalized = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $normalized) ?? $normalized;
        $normalized = strtr($normalized, [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ٱ' => 'ا',
            'ى' => 'ي',
            'ؤ' => 'و',
            'ئ' => 'ي',
        ]);

        return preg_replace('/\s+/u', ' ', trim($normalized)) ?? trim($normalized);
    }

    private function storeIdForOwner(int $ownerId): int
    {
        if (! $this->repository->isStoreOwner($ownerId)) {
            throw ValidationException::withMessages([
                'account_type' => [__('messages.profile.store_type_required')],
            ]);
        }

        return $this->repository->storeIdForOwner($ownerId);
    }
}

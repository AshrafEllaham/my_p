<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Enums\CompetitionStatusEnum;
use App\Models\Sai\Competition;
use App\Models\Sai\CompetitionEntry;
use App\Repositories\Sai\CompetitionEntryRepository;
use App\Repositories\Sai\CompetitionRepository;
use App\Repositories\Sai\UserRepository;
use App\Support\CompetitionAnswerNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserCompetitionService
{
    public function __construct(
        private readonly CompetitionRepository $competitions,
        private readonly CompetitionEntryRepository $entries,
        private readonly UserRepository $users,
        private readonly CompetitionAnswerNormalizer $answerNormalizer,
    ) {}

    public function details(int $userId, int|string $competitionId): Competition
    {
        return $this->competitions->findDetailsForUser($competitionId, $userId);
    }

    public function saveAnswer(int $userId, int|string $competitionId, string $answer): CompetitionEntry
    {
        $user = $this->users->findOrFail($userId);

        if ($user->account_type !== AccountTypeEnum::User) {
            throw ValidationException::withMessages([
                'account_type' => [__('messages.profile.user_type_required')],
            ]);
        }

        $answer = trim($answer);
        $normalizedAnswer = $this->answerNormalizer->normalize($answer);

        if ($normalizedAnswer === '') {
            throw ValidationException::withMessages([
                'answer' => [__('messages.validation.user_competitions.answer.regex')],
            ]);
        }

        return DB::transaction(function () use ($userId, $competitionId, $answer, $normalizedAnswer): CompetitionEntry {
            $competition = $this->competitions->findForUserForUpdate($competitionId);

            if ($competition->status !== CompetitionStatusEnum::Active || ! $competition->ends_at->isFuture()) {
                throw ValidationException::withMessages([
                    'competition' => [__('messages.user_competitions.participation_closed')],
                ]);
            }

            $entry = $this->entries->findForCompetitionAndUserForUpdate($competition->id, $userId);

            return $this->entries->saveUserAnswer(
                $entry,
                $competition->id,
                $userId,
                $answer,
                $normalizedAnswer,
            );
        });
    }
}

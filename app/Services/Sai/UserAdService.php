<?php

namespace App\Services\Sai;

use App\Enums\AdInteractionEventTypeEnum;
use App\Enums\AdPlacementEnum;
use App\Repositories\Sai\AdDailyMetricRepository;
use App\Repositories\Sai\AdInteractionEventRepository;
use App\Repositories\Sai\AdRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class UserAdService
{
    public function __construct(
        private readonly AdRepository $ads,
        private readonly AdInteractionEventRepository $events,
        private readonly AdDailyMetricRepository $dailyMetrics,
        private readonly DatabaseManager $database,
    ) {}

    /** @param array{placement: string} $filters */
    public function list(array $filters): Builder
    {
        return $this->ads->listActiveForPlacement(AdPlacementEnum::from($filters['placement']));
    }

    /** @return array{event_id: string, type: AdInteractionEventTypeEnum, recorded: bool} */
    public function recordInteraction(
        int $userId,
        int $adId,
        string $eventId,
        AdInteractionEventTypeEnum $type,
    ): array {
        return $this->database->transaction(function () use ($userId, $adId, $eventId, $type): array {
            $existingEvent = $this->events->findByEventId($eventId);
            if ($existingEvent !== null) {
                if (
                    $existingEvent['ad_id'] !== $adId
                    || $existingEvent['user_id'] !== $userId
                    || $existingEvent['type'] !== $type
                ) {
                    throw ValidationException::withMessages([
                        'event_id' => [__('messages.ads.event_id_conflict')],
                    ]);
                }

                return ['event_id' => $eventId, 'type' => $type, 'recorded' => false];
            }

            $this->ads->findActiveForInteractionOrFail($adId);
            $created = $this->events->createIfMissing($eventId, $adId, $userId, $type);

            if (! $created) {
                $existingEvent = $this->events->findByEventIdForUpdate($eventId);
                if (
                    $existingEvent === null
                    || $existingEvent['ad_id'] !== $adId
                    || $existingEvent['user_id'] !== $userId
                    || $existingEvent['type'] !== $type
                ) {
                    throw ValidationException::withMessages([
                        'event_id' => [__('messages.ads.event_id_conflict')],
                    ]);
                }

                return ['event_id' => $eventId, 'type' => $type, 'recorded' => false];
            }

            $this->dailyMetrics->incrementForInteraction($adId, $type, today()->toDateString());

            return ['event_id' => $eventId, 'type' => $type, 'recorded' => true];
        });
    }
}

<?php

namespace App\Repositories\Sai;

use App\Enums\AdInteractionEventTypeEnum;
use App\Models\Sai\AdInteractionEvent;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class AdInteractionEventRepository extends MainRepository
{
    public function __construct(AdInteractionEvent $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    /** @return array{event_id: string, ad_id: int, user_id: int, type: AdInteractionEventTypeEnum}|null */
    public function findByEventId(string $eventId): ?array
    {
        $event = $this->query()
            ->select(['event_id', 'ad_id', 'user_id', 'type'])
            ->where('event_id', $eventId)
            ->first();

        return $this->snapshot($event);
    }

    /** @return array{event_id: string, ad_id: int, user_id: int, type: AdInteractionEventTypeEnum}|null */
    public function findByEventIdForUpdate(string $eventId): ?array
    {
        $event = $this->query()
            ->select(['event_id', 'ad_id', 'user_id', 'type'])
            ->where('event_id', $eventId)
            ->lockForUpdate()
            ->first();

        return $this->snapshot($event);
    }

    /** @return array{event_id: string, ad_id: int, user_id: int, type: AdInteractionEventTypeEnum}|null */
    private function snapshot(?AdInteractionEvent $event): ?array
    {
        if ($event === null) {
            return null;
        }

        return [
            'event_id' => (string) $event->event_id,
            'ad_id' => (int) $event->ad_id,
            'user_id' => (int) $event->user_id,
            'type' => $event->type,
        ];
    }

    public function createIfMissing(string $eventId, int $adId, int $userId, AdInteractionEventTypeEnum $type): bool
    {
        $now = now();

        return $this->getModel()->newQuery()->toBase()->insertOrIgnore([
            'event_id' => $eventId,
            'ad_id' => $adId,
            'user_id' => $userId,
            'type' => $type->value,
            'created_at' => $now,
            'updated_at' => $now,
        ]) === 1;
    }
}

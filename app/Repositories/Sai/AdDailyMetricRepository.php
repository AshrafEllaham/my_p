<?php

namespace App\Repositories\Sai;

use App\Enums\AdInteractionEventTypeEnum;
use App\Models\Sai\AdDailyMetric;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdDailyMetricRepository extends MainRepository
{
    public function __construct(AdDailyMetric $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function incrementForInteraction(int $adId, AdInteractionEventTypeEnum $type, string $metricDate): void
    {
        $now = now();
        $this->getModel()->newQuery()->toBase()->insertOrIgnore([
            'ad_id' => $adId,
            'metric_date' => $metricDate,
            'impressions' => 0,
            'clicks' => 0,
            'chats_started' => 0,
            'orders_attributed' => 0,
            'revenue_attributed' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $column = match ($type) {
            AdInteractionEventTypeEnum::Impression => 'impressions',
            AdInteractionEventTypeEnum::Click => 'clicks',
        };

        $this->query()
            ->where('ad_id', $adId)
            ->where('metric_date', $metricDate)
            ->increment($column);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createRecord(array $data): Model
    {
        $record = $this->getModel()->newInstance();
        $record->fill($data);
        $record->save();

        return $record;
    }

    /** @param array<string, mixed> $data */
    public function updateRecord(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $record;
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}

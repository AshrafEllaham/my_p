<?php

namespace App\Repositories\Sai;

use App\Enums\UserReportTypeEnum;
use App\Models\Sai\UserReport;
use App\Repositories\MainRepository;

class UserReportRepository extends MainRepository
{
    public function __construct(UserReport $model)
    {
        $this->model = $model;
    }

    public function submit(int $userId, UserReportTypeEnum $type, int $itemId, int $reasonId): int
    {
        return (int) $this->getModel()->newQuery()->createOrFirst([
            'user_id' => $userId,
            'type' => $type,
            'item_id' => $itemId,
            'report_reason_id' => $reasonId,
        ])->id;
    }
}

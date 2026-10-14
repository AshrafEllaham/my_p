<?php

namespace App\Models\Sai;

use App\Enums\UserReportTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    protected $fillable = ['user_id', 'type', 'item_id', 'report_reason_id'];

    protected function casts(): array
    {
        return ['type' => UserReportTypeEnum::class, 'item_id' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReportReason::class, 'report_reason_id');
    }
}

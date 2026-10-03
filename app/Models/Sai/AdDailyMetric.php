<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdDailyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'metric_date',
        'impressions',
        'clicks',
        'chats_started',
        'orders_attributed',
        'revenue_attributed',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'chats_started' => 'integer',
            'orders_attributed' => 'integer',
            'revenue_attributed' => 'decimal:2',
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }
}

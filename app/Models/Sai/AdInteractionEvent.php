<?php

namespace App\Models\Sai;

use App\Enums\AdInteractionEventTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdInteractionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'ad_id',
        'user_id',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => AdInteractionEventTypeEnum::class,
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

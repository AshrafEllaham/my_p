<?php

namespace App\Models\Sai;

use App\Enums\DisputeStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderDispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'order_id',
        'opened_by',
        'store_id',
        'status',
        'reason',
        'details',
        'resolution_note',
        'resolved_by_type',
        'resolved_by_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DisputeStatusEnum::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function opened(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    public function resolvedBy(): MorphTo
    {
        return $this->morphTo();
    }
}

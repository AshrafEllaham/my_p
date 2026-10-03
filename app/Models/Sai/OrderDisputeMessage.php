<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderDisputeMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_dispute_id',
        'sender_type',
        'sender_id',
        'body',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    public function orderDispute(): BelongsTo
    {
        return $this->belongsTo(OrderDispute::class, 'order_dispute_id');
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }
}

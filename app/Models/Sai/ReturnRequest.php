<?php

namespace App\Models\Sai;

use App\Enums\ReturnResolutionTypeEnum;
use App\Enums\ReturnStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'order_id',
        'user_id',
        'store_id',
        'status',
        'reason',
        'details',
        'requested_amount',
        'approved_amount',
        'resolution_type',
        'merchant_note',
        'decided_at',
        'product_received_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReturnStatusEnum::class,
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'resolution_type' => ReturnResolutionTypeEnum::class,
            'decided_at' => 'datetime',
            'product_received_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }
}

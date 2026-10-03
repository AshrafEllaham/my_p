<?php

namespace App\Models\Sai;

use App\Enums\SettlementStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'store_id',
        'wallet_id',
        'store_bank_account_id',
        'status',
        'gross_amount',
        'fee_amount',
        'net_amount',
        'currency',
        'idempotency_key',
        'bank_reference',
        'review_note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SettlementStatusEnum::class,
            'gross_amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}

<?php

namespace App\Models\Sai;

use App\Enums\WalletTransactionStatusEnum;
use App\Enums\WalletTransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'wallet_id',
        'counterparty_wallet_id',
        'reference_type',
        'reference_id',
        'type',
        'status',
        'amount',
        'balance_before',
        'balance_after',
        'currency',
        'idempotency_key',
        'provider_reference',
        'metadata',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => WalletTransactionTypeEnum::class,
            'status' => WalletTransactionStatusEnum::class,
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'metadata' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function counterpartyWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'counterparty_wallet_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}

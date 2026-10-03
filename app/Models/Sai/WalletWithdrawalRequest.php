<?php

namespace App\Models\Sai;

use App\Enums\PayoutMethodEnum;
use App\Enums\RequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletWithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'wallet_id',
        'wallet_transaction_id',
        'method',
        'destination',
        'amount',
        'currency',
        'status',
        'idempotency_key',
        'review_note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'method' => PayoutMethodEnum::class,
            'amount' => 'decimal:2',
            'status' => RequestStatusEnum::class,
            'processed_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }
}

<?php

namespace App\Models\Sai;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'order_id',
        'wallet_transaction_id',
        'status',
        'method',
        'amount',
        'currency',
        'idempotency_key',
        'provider_reference',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatusEnum::class,
            'method' => PaymentMethodEnum::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }
}

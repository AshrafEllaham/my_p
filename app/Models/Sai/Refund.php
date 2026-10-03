<?php

namespace App\Models\Sai;

use App\Enums\WalletTransactionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'order_id',
        'payment_id',
        'return_request_id',
        'order_dispute_id',
        'wallet_transaction_id',
        'status',
        'amount',
        'currency',
        'idempotency_key',
        'reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WalletTransactionStatusEnum::class,
            'amount' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class, 'return_request_id');
    }

    public function orderDispute(): BelongsTo
    {
        return $this->belongsTo(OrderDispute::class, 'order_dispute_id');
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }
}

<?php

namespace App\Models\Sai;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'number',
        'user_id',
        'store_id',
        'coupon_id',
        'store_policy_version_id',
        'status',
        'payment_status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'platform_fee_amount',
        'merchant_net_amount',
        'currency',
        'policy_snapshot',
        'pickup_qr_token',
        'pickup_code_hash',
        'pickup_expires_at',
        'confirmed_at',
        'preparing_at',
        'ready_at',
        'customer_confirmation_requested_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'payment_status' => PaymentStatusEnum::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'platform_fee_amount' => 'decimal:2',
            'merchant_net_amount' => 'decimal:2',
            'policy_snapshot' => 'array',
            'pickup_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'customer_confirmation_requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function storePolicyVersion(): BelongsTo
    {
        return $this->belongsTo(StorePolicyVersion::class, 'store_policy_version_id');
    }
}

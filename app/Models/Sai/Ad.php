<?php

namespace App\Models\Sai;

use App\Enums\AdActionEnum;
use App\Enums\AdPlacementEnum;
use App\Enums\AdStatusEnum;
use App\Enums\MediaTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'public_id',
        'store_id',
        'ad_package_id',
        'product_id',
        'category_id',
        'wallet_transaction_id',
        'title',
        'action_label',
        'caption',
        'placement',
        'action',
        'media_type',
        'media_path',
        'status',
        'cost',
        'currency',
        'starts_at',
        'ends_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'placement' => AdPlacementEnum::class,
            'action' => AdActionEnum::class,
            'media_type' => MediaTypeEnum::class,
            'status' => AdStatusEnum::class,
            'cost' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function adPackage(): BelongsTo
    {
        return $this->belongsTo(AdPackage::class, 'ad_package_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }
}

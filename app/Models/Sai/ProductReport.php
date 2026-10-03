<?php

namespace App\Models\Sai;

use App\Enums\ProductReportReasonEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'reason',
        'details',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'reason' => ProductReportReasonEnum::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

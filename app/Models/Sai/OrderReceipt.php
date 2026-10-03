<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'receipt_number',
        'snapshot',
        'pdf_path',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

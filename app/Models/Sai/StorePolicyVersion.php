<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePolicyVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'version',
        'pickup_hold_hours',
        'return_window_days',
        'returns_enabled',
        'exchanges_enabled',
        'cancellation_enabled',
        'pickup_instructions',
        'return_terms',
        'exchange_terms',
        'cancellation_terms',
        'is_current',
        'effective_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'pickup_hold_hours' => 'integer',
            'return_window_days' => 'integer',
            'returns_enabled' => 'boolean',
            'exchanges_enabled' => 'boolean',
            'cancellation_enabled' => 'boolean',
            'is_current' => 'boolean',
            'effective_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }
}

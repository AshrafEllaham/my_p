<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'orders_enabled',
        'pickup_enabled',
        'returns_enabled',
        'chats_enabled',
        'offers_enabled',
    ];

    protected function casts(): array
    {
        return [
            'orders_enabled' => 'boolean',
            'pickup_enabled' => 'boolean',
            'returns_enabled' => 'boolean',
            'chats_enabled' => 'boolean',
            'offers_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

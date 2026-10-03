<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'permissions',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }
}

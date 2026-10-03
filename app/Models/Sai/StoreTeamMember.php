<?php

namespace App\Models\Sai;

use App\Enums\TeamMemberStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreTeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'store_role_id',
        'status',
        'invited_by',
        'invited_at',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TeamMemberStatusEnum::class,
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function storeRole(): BelongsTo
    {
        return $this->belongsTo(StoreRole::class, 'store_role_id');
    }

    public function invited(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}

<?php

namespace App\Models\Sai;

use App\Enums\ConversationSafetyActionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationSafetyAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'actor_id',
        'target_id',
        'action',
        'reason',
        'details',
    ];

    protected function casts(): array
    {
        return ['action' => ConversationSafetyActionTypeEnum::class];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}

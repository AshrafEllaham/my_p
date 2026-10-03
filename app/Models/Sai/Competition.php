<?php

namespace App\Models\Sai;

use App\Enums\CompetitionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'public_id',
        'store_id',
        'winner_user_id',
        'content_locale',
        'title',
        'question',
        'prize',
        'image',
        'status',
        'correct_answer',
        'normalized_correct_answer',
        'ends_at',
        'drawn_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CompetitionStatusEnum::class,
            'ends_at' => 'datetime',
            'drawn_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function winnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }
}

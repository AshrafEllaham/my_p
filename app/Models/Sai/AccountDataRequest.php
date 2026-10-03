<?php

namespace App\Models\Sai;

use App\Enums\RequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountDataRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'user_id',
        'generated_document_id',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatusEnum::class,
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function generatedDocument(): BelongsTo
    {
        return $this->belongsTo(GeneratedDocument::class, 'generated_document_id');
    }
}

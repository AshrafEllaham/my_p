<?php

namespace App\Models\Sai;

use App\Enums\GeneratedDocumentTypeEnum;
use App\Enums\RequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneratedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'owner_type',
        'owner_id',
        'source_type',
        'source_id',
        'type',
        'status',
        'title',
        'file_path',
        'period_from',
        'period_to',
        'metadata',
        'generated_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => GeneratedDocumentTypeEnum::class,
            'status' => RequestStatusEnum::class,
            'period_from' => 'date',
            'period_to' => 'date',
            'metadata' => 'array',
            'generated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}

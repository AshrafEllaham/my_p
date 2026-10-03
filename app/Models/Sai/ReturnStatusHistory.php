<?php

namespace App\Models\Sai;

use App\Enums\ReturnStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReturnStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_request_id',
        'from_status',
        'to_status',
        'actor_type',
        'actor_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => ReturnStatusEnum::class,
            'to_status' => ReturnStatusEnum::class,
        ];
    }

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class, 'return_request_id');
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}

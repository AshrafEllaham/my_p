<?php

namespace App\Models\Sai;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = [
        'governorate_id',
        'is_active',
    ];

    public array $translatedAttributes = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'governorate_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }
}

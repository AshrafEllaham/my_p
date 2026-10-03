<?php

namespace App\Models\Sai;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdPackage extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    public array $translatedAttributes = [
        'name',
        'description',
    ];

    protected $fillable = [
        'code',
        'duration_days',
        'price',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

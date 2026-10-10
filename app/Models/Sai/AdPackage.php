<?php

namespace App\Models\Sai;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class, 'ad_package_id');
    }
}

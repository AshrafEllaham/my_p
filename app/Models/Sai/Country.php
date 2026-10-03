<?php

namespace App\Models\Sai;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = [
        'code',
        'phone_code',
        'flag',
        'is_active',
    ];

    public array $translatedAttributes = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function governorates(): HasMany
    {
        return $this->hasMany(Governorate::class);
    }
}

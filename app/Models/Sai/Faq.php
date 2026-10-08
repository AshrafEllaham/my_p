<?php

namespace App\Models\Sai;

use App\Enums\AccountTypeEnum;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    public array $translatedAttributes = [
        'question',
        'answer',
    ];

    protected $fillable = [
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountTypeEnum::class,
        ];
    }
}

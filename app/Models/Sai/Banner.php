<?php

namespace App\Models\Sai;

use App\Enums\AccountTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'file',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountTypeEnum::class,
        ];
    }
}

<?php

namespace App\Models\Twenty;

use Illuminate\Database\Eloquent\Model;

class CountryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}

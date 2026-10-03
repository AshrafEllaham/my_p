<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Model;

class CountryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}

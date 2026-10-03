<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Model;

class AdPackageTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];
}

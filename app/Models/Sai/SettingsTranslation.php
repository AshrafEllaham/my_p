<?php

namespace App\Models\Sai;

use Illuminate\Database\Eloquent\Model;

class SettingsTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'website_name',
        'about_app',
        'privacy',
        'terms_conditions',
    ];
}

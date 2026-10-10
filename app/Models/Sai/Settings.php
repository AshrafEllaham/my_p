<?php

namespace App\Models\Sai;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model implements TranslatableContract
{
    use Translatable;

    public array $translatedAttributes = [
        'website_name',
        'about_app',
        'privacy',
        'terms_conditions',
    ];

    protected $fillable = [
        'fav_icon',
        'logo_header',
        'logo_footer',
        'whatsapp',
        'phone',
        'other_phone',
        'email',
        'facebook',
        'instagram',
        'ad_home_price',
        'ad_category_price',
    ];

    protected function casts(): array
    {
        return [
            'ad_home_price' => 'decimal:2',
            'ad_category_price' => 'decimal:2',
        ];
    }
}

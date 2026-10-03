<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AdPlacementEnum: string
{
    use HasValues;

    case Home = 'home';
    case Category = 'category';
    case Storefront = 'storefront';
}

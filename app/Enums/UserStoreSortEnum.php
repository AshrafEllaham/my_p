<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum UserStoreSortEnum: string
{
    use HasValues;

    case HighestRated = 'high_rate';
    case Nearest = 'nearest';
    case Newest = 'newest';
}

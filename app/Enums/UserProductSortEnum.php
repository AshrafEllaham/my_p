<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum UserProductSortEnum: string
{
    use HasValues;

    case MostPurchased = 'most_purchased';
    case HighestRated = 'highest_rated';
}

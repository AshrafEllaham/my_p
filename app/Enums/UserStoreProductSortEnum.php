<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum UserStoreProductSortEnum: string
{
    use HasValues;

    case HighestRated = 'high_rate';
    case Newest = 'newest';
    case MostPurchased = 'most_purchased';
}

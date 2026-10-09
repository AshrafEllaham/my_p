<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ProductOrderByEnum: string
{
    use HasValues;

    case Recent = 'recent';
    case MostOrdered = 'orders';
    case MostViewed = 'views';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ProductStockFilterEnum: string
{
    use HasValues;

    case All = 'all';
    case Available = 'available';
    case Low = 'low';
    case Out = 'out';
}

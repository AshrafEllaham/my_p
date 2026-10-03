<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ReturnResolutionTypeEnum: string
{
    use HasValues;

    case Refund = 'refund';
    case Exchange = 'exchange';
}

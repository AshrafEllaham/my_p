<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AccountTypeEnum: string
{
    use HasValues;

    case Personal = 'personal';
    case Merchant = 'merchant';
}

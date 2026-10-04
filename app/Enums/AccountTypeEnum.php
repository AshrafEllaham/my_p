<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AccountTypeEnum: string
{
    use HasValues;

    case User = 'user';
    case Store = 'store';
}

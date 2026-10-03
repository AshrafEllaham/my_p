<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum PaymentMethodEnum: string
{
    use HasValues;

    case Wallet = 'wallet';
}

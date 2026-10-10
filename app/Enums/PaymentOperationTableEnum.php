<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum PaymentOperationTableEnum: string
{
    use HasValues;

    case PayTrip = 'pay_trip';
}
